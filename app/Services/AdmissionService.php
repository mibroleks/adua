<?php

/*
Component: Admission Service
File Path: app/Services/AdmissionService.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Manages the admission application lifecycle, including:
- Draft application creation
- Application submission
- Application-processing status transitions
- Institutional admission outcome decisions
- Official admission offer issuance and withdrawal
- Admission decision history
- Document verification and replacement
- Application and document history
- Referral partner attribution

Architecture:
- Referral attribution is copied from the registered User.
- Application referral attribution is a historical snapshot.
- Referral attribution does not affect admission eligibility,
  application fees, payment processing, or admission decisions.
- Application approval and institutional admission are separate decisions.
- Approved applications enter admission consideration as PENDING.
- Admission offers are issued only when the institutional outcome
  becomes ADMITTED.
- Issued offers are preserved as historical document records.
- Changing an ADMITTED outcome withdraws its active offer.
- Admission outcomes and their history are recorded centrally.
- Existing application lifecycle history is recorded centrally.
- Existing document and referral workflows remain unchanged.
- Official offer notifications are dispatched only after the
  admission outcome and offer issuance transaction commits.

Status: Integration / Hardening
Version: 3.2 (official offer notification integration)
*/

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ApplicationDocumentHistory;
use App\Models\ApplicationDocumentType;
use App\Models\AdmissionDecision;
use App\Models\AdmissionDecisionHistory;
use App\Models\ApplicationStatusHistory;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class AdmissionService
{
    protected NotificationService $notifications;

    protected AdmissionLetterService $admissionLetters;

    public function __construct(
        NotificationService $notifications,
        AdmissionLetterService $admissionLetters
    ) {
        $this->notifications = $notifications;
        $this->admissionLetters = $admissionLetters;
    }

    /**
     * Generate a unique application number.
     *
     * Format: ADM-{YEAR}-{SEQUENCE}
     * Example: ADM-2026-0001
     */
    protected function generateApplicationNumber(): string
    {
        $year = now()->year;

        // Count how many applications exist for this year.
        $count = Application::whereYear('created_at', $year)->count() + 1;

        // Pad with leading zeros for consistency (e.g., 0001, 0002).
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);

        return "ADM-{$year}-{$sequence}";
    }

    /**
     * Create a new draft application for a user.
     *
     * Referral attribution is copied from the registered user's account.
     * It is not accepted from application request input.
     */
    public function createDraft(User $user, int $programmeId): Application
    {
        $programme = Programme::where('active', true)
            ->where('application_enabled', true)
            ->findOrFail($programmeId);

        return Application::create([
            'application_number'  => $this->generateApplicationNumber(),
            'user_id'             => $user->id,
            'referral_partner_id' => $user->referral_partner_id,
            'programme_id'        => $programme->id,
            'application_fee'     => $programme->application_fee,
            'application_status'  => Application::STATUS_DRAFT,
            'payment_status'      => Application::PAYMENT_PENDING,
        ]);
    }

    public function missingRequiredDocuments(Application $application): array
    {
        $requiredDocs = ApplicationDocumentType::where(function ($query) use ($application) {
                $query->whereNull('programme_id')
                    ->orWhere('programme_id', $application->programme_id);
            })
            ->where('required', true)
            ->where('active', true)
            ->get();

        $missingDocs = [];

        foreach ($requiredDocs as $docType) {
            $doc = $application->documents()
                ->where('document_type_id', $docType->id)
                ->first();

            if (! $doc || $doc->status === 'REJECTED') {
                $missingDocs[] = $docType->name;
            }
        }

        return $missingDocs;
    }

    public function canSubmit(Application $application): bool
    {
        if ($application->application_status !== Application::STATUS_DRAFT) {
            return false;
        }

        if ($application->payment_status !== Application::PAYMENT_SUCCESS) {
            return false;
        }

        return empty($this->missingRequiredDocuments($application));
    }

    /**
     * Submit a draft application.
     *
     * Records exactly one DRAFT -> SUBMITTED lifecycle event.
     * The applicant is recorded as the actor for this transition.
     */
    public function submit(Application $application): Application
    {
        if ($application->application_status !== Application::STATUS_DRAFT) {
            throw new InvalidArgumentException(
                'Only draft applications can be submitted.'
            );
        }

        if ($application->payment_status !== Application::PAYMENT_SUCCESS) {
            throw new InvalidArgumentException(
                'Application payment must be successful before submission.'
            );
        }

        $missingDocs = $this->missingRequiredDocuments($application);

        if (! empty($missingDocs)) {
            throw new InvalidArgumentException(
                'Required documents are missing: ' . implode(', ', $missingDocs)
            );
        }

        return DB::transaction(function () use ($application) {
            $application->refresh();

            // Recheck the lifecycle state inside the transaction.
            if ($application->application_status !== Application::STATUS_DRAFT) {
                throw new InvalidArgumentException(
                    'Only draft applications can be submitted.'
                );
            }

            $oldStatus = $application->application_status;
            $newStatus = Application::STATUS_SUBMITTED;
            $changedAt = now();

            if ($application->application_fee === null) {
                $application->snapshotFee();
            }

            $application->submitted_at = $changedAt;
            $application->save();

            // Update status only. The model no longer creates history.
            $application->setApplicationStatus($newStatus);

            // The service records the lifecycle event exactly once.
            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'old_status'     => $oldStatus,
                'new_status'     => $newStatus,
                'changed_by'     => $application->user_id,
                'changed_at'     => $changedAt,
                'remarks'        => null,
            ]);

            // Notify applicant.
            $this->notifications->applicationSubmitted(
                $application->user,
                $application->application_number
            );

            return $application->fresh();
        });
    }

    /**
     * Change the application lifecycle status.
     *
     * This method is the single recording point for officer-driven
     * application status transitions.
     */
    public function changeStatus(
        Application $application,
        string $newStatus,
        ?int $officerId = null,
        ?string $remarks = null
    ): Application {
        $validTransitions = [
            Application::STATUS_DRAFT => [
                Application::STATUS_SUBMITTED,
            ],
            Application::STATUS_SUBMITTED => [
                Application::STATUS_UNDER_REVIEW,
                Application::STATUS_CORRECTION_REQUIRED,
            ],
            Application::STATUS_UNDER_REVIEW => [
                Application::STATUS_APPROVED,
                Application::STATUS_REJECTED,
                Application::STATUS_CORRECTION_REQUIRED,
            ],
            Application::STATUS_CORRECTION_REQUIRED => [
                Application::STATUS_SUBMITTED,
            ],
        ];

        $current = $application->application_status;

        if (
            ! isset($validTransitions[$current]) ||
            ! in_array($newStatus, $validTransitions[$current], true)
        ) {
            throw new InvalidArgumentException(
                "Invalid transition: {$current} → {$newStatus}"
            );
        }

        return DB::transaction(function () use (
            $application,
            $newStatus,
            $officerId,
            $remarks,
            $current
        ) {
            $application->refresh();

            // Protect against a stale application instance.
            if ($application->application_status !== $current) {
                throw new InvalidArgumentException(
                    'The application status has changed. Refresh the application and try again.'
                );
            }

            $changedAt = now();

            // Update status only. No history is created by the model.
            $application->setApplicationStatus($newStatus, $officerId);

            // Record the transition once, including actor and remarks.
            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'old_status'     => $current,
                'new_status'     => $newStatus,
                'changed_by'     => $officerId,
                'changed_at'     => $changedAt,
                'remarks'        => $remarks,
            ]);

            if ($newStatus === Application::STATUS_CORRECTION_REQUIRED) {
                $this->notifications->correctionRequired(
                    $application->user,
                    $application->application_number,
                    $remarks ?? ''
                );
            }

            if ($newStatus === Application::STATUS_APPROVED) {
                $this->notifications->applicationApproved(
                    $application->user,
                    $application->application_number
                );
            }

            if ($newStatus === Application::STATUS_REJECTED) {
                $this->notifications->applicationRejected(
                    $application->user,
                    $application->application_number,
                    $remarks
                );
            }

            return $application->fresh();
        });
    }

    /**
     * Record the application-processing decision.
     *
     * APPROVED means the application passed processing. It does not
     * mean the applicant has been admitted.
     *
     * An approved application receives a separate PENDING admission
     * outcome. A rejected application does not receive an admission offer.
     */
    public function recordDecision(
        Application $application,
        User $officer,
        string $decision,
        ?string $remarks = null
    ): AdmissionDecision {
        if (! in_array($decision, [
            AdmissionDecision::DECISION_APPROVED,
            AdmissionDecision::DECISION_REJECTED,
        ], true)) {
            throw new InvalidArgumentException(
                "Invalid application decision: {$decision}"
            );
        }

        return DB::transaction(function () use (
            $application,
            $officer,
            $decision,
            $remarks
        ) {
            /*
             * Lock the application so two officers cannot process
             * the same application decision concurrently.
             */
            $lockedApplication = Application::query()
                ->whereKey($application->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedApplication->application_status !== Application::STATUS_UNDER_REVIEW) {
                throw new InvalidArgumentException(
                    'Only applications under review can receive an application decision.'
                );
            }

            /*
             * Preserve the existing one-decision-per-application
             * workflow and prevent duplicate decision records.
             */
            $existingDecision = AdmissionDecision::query()
                ->where('application_id', $lockedApplication->id)
                ->lockForUpdate()
                ->first();

            if ($existingDecision) {
                throw new InvalidArgumentException(
                    'An application decision has already been recorded for this application.'
                );
            }

            $decidedAt = now();

            $decisionRecord = AdmissionDecision::create([
                'application_id' => $lockedApplication->id,
                'officer_id'     => $officer->id,
                'decision'       => $decision,
                'remarks'        => $remarks,
                'decided_at'     => $decidedAt,

                /*
                 * Only an application that passes processing
                 * enters institutional admission consideration.
                 */
                'admission_status' => $decision === AdmissionDecision::DECISION_APPROVED
                    ? AdmissionDecision::ADMISSION_STATUS_PENDING
                    : null,

                /*
                 * No admission officer or admission decision timestamp
                 * is assigned at this stage. The actual outcome is pending.
                 */
                'admission_officer_id' => null,
                'admission_decided_at' => null,
                'admission_remarks' => null,
            ]);

            $applicationStatus = $decision === AdmissionDecision::DECISION_APPROVED
                ? Application::STATUS_APPROVED
                : Application::STATUS_REJECTED;

            $this->changeStatus(
                $lockedApplication,
                $applicationStatus,
                $officer->id,
                $remarks
            );

            /*
             * Record the initial admission state separately.
             * This is not an admission offer; it records entry into
             * the institutional admission consideration queue.
             */
            if ($decision === AdmissionDecision::DECISION_APPROVED) {
                AdmissionDecisionHistory::create([
                    'admission_decision_id' => $decisionRecord->id,
                    'old_status'             => null,
                    'new_status'             => AdmissionDecision::ADMISSION_STATUS_PENDING,
                    'officer_id'             => null,
                    'remarks'                => 'Application approved. Awaiting institutional admission decision.',
                    'changed_at'             => $decidedAt,
                ]);
            }

            return $decisionRecord->fresh([
                'application',
                'officer',
                'admissionOfficer',
            ]);
        });
    }

    /**
     * Record the institution's actual admission outcome.
     *
     * This method is separate from approve() and reject(), which
     * only process the application itself.
     *
     * Only approved applications can receive an admission outcome.
     *
     * An ADMITTED outcome issues an official admission offer.
     * Changing an existing ADMITTED outcome withdraws its active offer.
     */
    public function recordAdmissionOutcome(
        AdmissionDecision $decisionRecord,
        User $officer,
        string $newStatus,
        ?string $remarks = null
    ): AdmissionDecision {
        $allowedOutcomes = [
            AdmissionDecision::ADMISSION_STATUS_ADMITTED,
            AdmissionDecision::ADMISSION_STATUS_NOT_ADMITTED,
            AdmissionDecision::ADMISSION_STATUS_WAITLISTED,
            AdmissionDecision::ADMISSION_STATUS_DEFERRED,
        ];

        if (! in_array($newStatus, $allowedOutcomes, true)) {
            throw new InvalidArgumentException(
                "Invalid institutional admission outcome: {$newStatus}"
            );
        }

        return DB::transaction(function () use (
            $decisionRecord,
            $officer,
            $newStatus,
            $remarks
        ) {
            /*
             * Lock the decision record to prevent concurrent
             * admission outcome changes.
             */
            $lockedDecision = AdmissionDecision::query()
                ->whereKey($decisionRecord->id)
                ->lockForUpdate()
                ->firstOrFail();

            $application = Application::query()
                ->whereKey($lockedDecision->application_id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * Admission consideration is restricted to applications
             * that passed the application-processing stage.
             */
            if (
                $lockedDecision->decision !== AdmissionDecision::DECISION_APPROVED ||
                $application->application_status !== Application::STATUS_APPROVED
            ) {
                throw new InvalidArgumentException(
                    'Only approved applications can receive an institutional admission outcome.'
                );
            }

            $oldStatus = $lockedDecision->admission_status;

            /*
             * Compatibility for approved applications whose decision
             * records existed before admission outcome fields were added.
             *
             * Their old APPROVED value is an application decision, not
             * an admission offer. Initialise their admission outcome as
             * PENDING and preserve that initial state in the history.
             */
            if ($oldStatus === null) {
                $initialisedAt = now();

                $lockedDecision->update([
                    'admission_status' => AdmissionDecision::ADMISSION_STATUS_PENDING,
                    'admission_officer_id' => null,
                    'admission_decided_at' => null,
                    'admission_remarks' => null,
                ]);

                AdmissionDecisionHistory::create([
                    'admission_decision_id' => $lockedDecision->id,
                    'old_status' => null,
                    'new_status' => AdmissionDecision::ADMISSION_STATUS_PENDING,
                    'officer_id' => null,
                    'remarks' => 'Existing approved application entered institutional admission consideration.',
                    'changed_at' => $initialisedAt,
                ]);

                $oldStatus = AdmissionDecision::ADMISSION_STATUS_PENDING;
            }

            if ($oldStatus === $newStatus) {
                throw new InvalidArgumentException(
                    'The application already has this admission outcome.'
                );
            }

            $changedAt = now();

            /*
             * Update the current admission outcome.
             * Previous outcomes remain available in the history table.
             */
            $lockedDecision->update([
                'admission_status'      => $newStatus,
                'admission_officer_id'  => $officer->id,
                'admission_decided_at'  => $changedAt,
                'admission_remarks'     => $remarks,
            ]);

            /*
             * Record the transition in the admission history.
             */
            AdmissionDecisionHistory::create([
                'admission_decision_id' => $lockedDecision->id,
                'old_status'             => $oldStatus,
                'new_status'             => $newStatus,
                'officer_id'             => $officer->id,
                'remarks'                => $remarks,
                'changed_at'             => $changedAt,
            ]);

            /*
             * Issue the official offer only after the institutional
             * outcome has successfully changed to ADMITTED.
             *
             * The letter is created inside the same database transaction.
             * If offer issuance fails, the outcome update and history
             * are rolled back as well.
             */
            if (
                $newStatus === AdmissionDecision::ADMISSION_STATUS_ADMITTED
            ) {
                $issuedLetter = $this->admissionLetters->issue(
                    $lockedDecision,
                    $officer
                );

                /*
                 * Notify the applicant about this exact issued letter.
                 *
                 * GenericNotification uses afterCommit(), so the
                 * database, broadcast and email notification jobs
                 * cannot run before the outermost transaction commits.
                 *
                 * The notification service also verifies that this
                 * record is an ISSUED admission offer before dispatch.
                 */
                $this->notifications->officialAdmissionOfferIssued(
                    $issuedLetter
                );
            }

            /*
             * If an already-admitted applicant receives another
             * institutional outcome, withdraw the active offer.
             *
             * The letter record and its content snapshot are retained.
             * No new official-offer email is sent for this transition.
             */
            elseif (
                $oldStatus === AdmissionDecision::ADMISSION_STATUS_ADMITTED
            ) {
                $this->admissionLetters->withdrawCurrentOffer(
                    $lockedDecision,
                    $officer,
                    'Institutional admission outcome changed from ADMITTED to '
                        . $newStatus
                        . ($remarks ? '. ' . $remarks : '.')
                );
            }

            return $lockedDecision->fresh([
                'application',
                'officer',
                'admissionOfficer',
                'admissionHistories.officer',
            ]);
        });
    }

    /**
     * Approve an application after processing.
     *
     * This does not grant admission. It creates a PENDING
     * institutional admission outcome for later consideration.
     */
    public function approve(
        Application $application,
        User $officer,
        ?string $remarks = null
    ): AdmissionDecision {
        return $this->recordDecision(
            $application,
            $officer,
            AdmissionDecision::DECISION_APPROVED,
            $remarks
        );
    }

    /**
     * Reject an application during application processing.
     *
     * This is not the same as an institutional NOT_ADMITTED outcome.
     */
    public function reject(
        Application $application,
        User $officer,
        ?string $remarks = null
    ): AdmissionDecision {
        return $this->recordDecision(
            $application,
            $officer,
            AdmissionDecision::DECISION_REJECTED,
            $remarks
        );
    }

    public function startReview(
        Application $application,
        ?int $officerId = null
    ): Application {
        return $this->changeStatus(
            $application,
            Application::STATUS_UNDER_REVIEW,
            $officerId
        );
    }

    public function requestCorrection(
        Application $application,
        User $officer,
        string $remarks
    ): Application {
        return $this->changeStatus(
            $application,
            Application::STATUS_CORRECTION_REQUIRED,
            $officer->id,
            $remarks
        );
    }

    protected function recordDocumentHistory(
        ApplicationDocument $document,
        string $action,
        ?string $oldStatus,
        ?string $newStatus,
        ?int $performedBy = null,
        ?string $remarks = null
    ): ApplicationDocumentHistory {
        return ApplicationDocumentHistory::create([
            'application_document_id' => $document->id,
            'application_id'          => $document->application_id,
            'action'                  => $action,
            'old_status'              => $oldStatus,
            'new_status'              => $newStatus,
            'performed_by'            => $performedBy,
            'remarks'                 => $remarks,
            'performed_at'            => now(),
        ]);
    }

    public function verifyDocument(
        ApplicationDocument $document,
        ?int $officerId = null
    ): ApplicationDocument {
        return DB::transaction(function () use ($document, $officerId) {
            $document->refresh();

            $oldStatus = $document->status;

            $document->update([
                'status'           => 'VERIFIED',
                'verified_at'      => now(),
                'verified_by'      => $officerId,
                'rejection_reason' => null,
            ]);

            $this->recordDocumentHistory(
                $document,
                'VERIFIED',
                $oldStatus,
                'VERIFIED',
                $officerId
            );

            return $document->fresh();
        });
    }

    public function rejectDocument(
        ApplicationDocument $document,
        ?int $officerId = null,
        ?string $reason = null
    ): ApplicationDocument {
        return DB::transaction(function () use ($document, $officerId, $reason) {
            $document->refresh();

            $oldStatus = $document->status;

            $document->update([
                'status'           => 'REJECTED',
                'verified_at'      => now(),
                'verified_by'      => $officerId,
                'rejection_reason' => $reason,
            ]);

            $this->recordDocumentHistory(
                $document,
                'REJECTED',
                $oldStatus,
                'REJECTED',
                $officerId,
                $reason
            );

            // Notify applicant of document rejection.
            $this->notifications->documentRejected(
                $document->application->user,
                $document->original_name,
                $reason
            );

            return $document->fresh();
        });
    }

    /**
     * Replace a rejected document.
     */
    public function replaceDocument(
        ApplicationDocument $document,
        \Illuminate\Http\UploadedFile $file,
        ?int $userId = null
    ): ApplicationDocument {
        if ($document->status !== 'REJECTED') {
            throw new InvalidArgumentException(
                'Only rejected documents can be replaced.'
            );
        }

        return DB::transaction(function () use ($document, $file, $userId) {
            $document->refresh();

            $oldStatus = $document->status;
            $oldPath = $document->path;

            // Store new file.
            $path = $file->store(
                'applications/' . $document->application_id,
                'public'
            );

            // Update document metadata and reset review fields.
            $document->update([
                'path'             => $path,
                'disk'             => 'public',
                'original_name'    => $file->getClientOriginalName(),
                'mime_type'        => $file->getClientMimeType(),
                'size'             => $file->getSize(),
                'status'           => 'PENDING',
                'uploaded_at'      => now(),
                'verified_at'      => null,
                'verified_by'      => null,
                'rejection_reason' => null,
            ]);

            // Record history atomically with remarks.
            $this->recordDocumentHistory(
                $document,
                'REPLACED',
                $oldStatus,
                'PENDING',
                $userId,
                'Applicant replaced rejected document.'
            );

            // Delete old file safely if different.
            if ($oldPath && $oldPath !== $path) {
                try {
                    Storage::disk($document->disk ?? 'public')->delete($oldPath);
                } catch (\Throwable $e) {
                    \Log::warning("Failed to delete old document file: {$oldPath}", [
                        'document_id' => $document->id,
                        'error'       => $e->getMessage(),
                    ]);
                }
            }

            // Notify applicant of document replacement.
            // This remains database + broadcast only.
            $this->notifications->notify(
                $document->application->user,
                'Document Replaced',
                'You have replaced a rejected document: ' . $document->original_name,
                route('applications.documents'),
                'document'
            );

            return $document->fresh();
        });
    }
}
