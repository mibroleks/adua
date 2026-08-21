<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ApplicationDocumentHistory;
use App\Models\ApplicationDocumentType;
use App\Models\AdmissionDecision;
use App\Models\ApplicationStatusHistory;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class AdmissionService
{
    protected NotificationService $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * Generate a unique application number.
     * Format: ADM-{YEAR}-{SEQUENCE}
     * Example: ADM-2026-0001
     */
    protected function generateApplicationNumber(): string
    {
        $year = now()->year;

        // Count how many applications exist for this year
        $count = Application::whereYear('created_at', $year)->count() + 1;

        // Pad with leading zeros for consistency (e.g., 0001, 0002)
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);

        return "ADM-{$year}-{$sequence}";
    }

    /**
     * Create a new draft application for a user.
     */
    public function createDraft(User $user, int $programmeId): Application
    {
        $programme = Programme::where('active', true)
            ->where('application_enabled', true)
            ->findOrFail($programmeId);

        return Application::create([
            'application_number' => $this->generateApplicationNumber(),
            'user_id'            => $user->id,
            'programme_id'       => $programme->id,
            'application_fee'    => $programme->application_fee,
            'application_status' => Application::STATUS_DRAFT,
            'payment_status'     => Application::PAYMENT_PENDING,
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
        if ($application->application_status !== Application::STATUS_DRAFT) return false;
        if ($application->payment_status !== Application::PAYMENT_SUCCESS) return false;
        return empty($this->missingRequiredDocuments($application));
    }

    public function submit(Application $application): Application
    {
        if ($application->application_status !== Application::STATUS_DRAFT) {
            throw new InvalidArgumentException('Only draft applications can be submitted.');
        }
        if ($application->payment_status !== Application::PAYMENT_SUCCESS) {
            throw new InvalidArgumentException('Application payment must be successful before submission.');
        }
        $missingDocs = $this->missingRequiredDocuments($application);
        if (! empty($missingDocs)) {
            throw new InvalidArgumentException('Required documents are missing: ' . implode(', ', $missingDocs));
        }

        return DB::transaction(function () use ($application) {
            $application->refresh();
            if ($application->application_fee === null) {
                $application->snapshotFee();
            }
            $application->submitted_at = now();
            $application->save();
            $application->setApplicationStatus(Application::STATUS_SUBMITTED);

            // 🔔 Notify applicant
            $this->notifications->applicationSubmitted($application->user, $application->application_number);

            return $application->fresh();
        });
    }

    public function changeStatus(Application $application, string $newStatus, ?int $officerId = null, ?string $remarks = null): Application
    {
        $validTransitions = [
            Application::STATUS_DRAFT => [Application::STATUS_SUBMITTED],
            Application::STATUS_SUBMITTED => [Application::STATUS_UNDER_REVIEW, Application::STATUS_CORRECTION_REQUIRED],
            Application::STATUS_UNDER_REVIEW => [Application::STATUS_APPROVED, Application::STATUS_REJECTED, Application::STATUS_CORRECTION_REQUIRED],
            Application::STATUS_CORRECTION_REQUIRED => [Application::STATUS_SUBMITTED],
        ];

        $current = $application->application_status;
        if (! isset($validTransitions[$current]) || ! in_array($newStatus, $validTransitions[$current])) {
            throw new InvalidArgumentException("Invalid transition: {$current} → {$newStatus}");
        }

        return DB::transaction(function () use ($application, $newStatus, $officerId, $remarks, $current) {
            $application->setApplicationStatus($newStatus, $officerId);

            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'old_status'     => $current,
                'new_status'     => $newStatus,
                'changed_by'     => $officerId,
                'changed_at'     => now(),
                'remarks'        => $remarks,
            ]);

            if ($newStatus === Application::STATUS_CORRECTION_REQUIRED) {
                $this->notifications->correctionRequired($application->user, $application->application_number, $remarks ?? '');
            }
            if ($newStatus === Application::STATUS_APPROVED) {
                $this->notifications->applicationApproved($application->user, $application->application_number);
            }
            if ($newStatus === Application::STATUS_REJECTED) {
                $this->notifications->applicationRejected($application->user, $application->application_number, $remarks);
            }

            return $application->fresh();
        });
    }

    public function recordDecision(Application $application, User $officer, string $decision, ?string $remarks = null): AdmissionDecision
    {
        if (! in_array($decision, [AdmissionDecision::DECISION_APPROVED, AdmissionDecision::DECISION_REJECTED])) {
            throw new InvalidArgumentException("Invalid decision: {$decision}");
        }

        return DB::transaction(function () use ($application, $officer, $decision, $remarks) {
            $decisionRecord = AdmissionDecision::create([
                'application_id' => $application->id,
                'officer_id'     => $officer->id,
                'decision'       => $decision,
                'remarks'        => $remarks,
                'decided_at'     => now(),
            ]);

            $status = $decision === AdmissionDecision::DECISION_APPROVED
                ? Application::STATUS_APPROVED
                : Application::STATUS_REJECTED;

            $this->changeStatus($application, $status, $officer->id, $remarks);

            return $decisionRecord;
        });
    }

    public function approve(Application $application, User $officer, ?string $remarks = null): AdmissionDecision
    {
        return $this->recordDecision($application, $officer, AdmissionDecision::DECISION_APPROVED, $remarks);
    }

    public function reject(Application $application, User $officer, ?string $remarks = null): AdmissionDecision
    {
        return $this->recordDecision($application, $officer, AdmissionDecision::DECISION_REJECTED, $remarks);
    }

    public function startReview(Application $application, ?int $officerId = null): Application
    {
        return $this->changeStatus($application, Application::STATUS_UNDER_REVIEW, $officerId);
    }

    public function requestCorrection(Application $application, User $officer, string $remarks): Application
    {
        return $this->changeStatus($application, Application::STATUS_CORRECTION_REQUIRED, $officer->id, $remarks);
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


    public function verifyDocument(ApplicationDocument $document, ?int $officerId = null): ApplicationDocument
    {
        return DB::transaction(function () use ($document, $officerId) {
            $document->refresh();
            $oldStatus = $document->status;

            $document->update([
                'status'           => 'VERIFIED',
                'verified_at'      => now(),
                'verified_by'      => $officerId,
                'rejection_reason' => null,
            ]);

            $this->recordDocumentHistory($document, 'VERIFIED', $oldStatus, 'VERIFIED', $officerId);

            return $document->fresh();
        });
    }

    public function rejectDocument(ApplicationDocument $document, ?int $officerId = null, ?string $reason = null): ApplicationDocument
    {
        return DB::transaction(function () use ($document, $officerId, $reason) {
            $document->refresh();
            $oldStatus = $document->status;

            $document->update([
                'status'           => 'REJECTED',
                'verified_at'      => now(),
                'verified_by'      => $officerId,
                'rejection_reason' => $reason,
            ]);

            $this->recordDocumentHistory($document, 'REJECTED', $oldStatus, 'REJECTED', $officerId, $reason);

            // 🔔 Notify applicant of document rejection
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
    public function replaceDocument(ApplicationDocument $document, \Illuminate\Http\UploadedFile $file, ?int $userId = null): ApplicationDocument
    {
        if ($document->status !== 'REJECTED') {
            throw new InvalidArgumentException('Only rejected documents can be replaced.');
        }

        return DB::transaction(function () use ($document, $file, $userId) {
            $document->refresh();
            $oldStatus = $document->status;
            $oldPath   = $document->path;

            // Store new file
            $path = $file->store('applications/' . $document->application_id, 'public');

            // Update document metadata and reset review fields
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

            // Record history atomically with remarks
            $this->recordDocumentHistory(
                $document,
                'REPLACED',
                $oldStatus,
                'PENDING',
                $userId,
                'Applicant replaced rejected document.'
            );

            // Delete old file safely if different
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

            // 🔔 Notify applicant of document replacement
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



