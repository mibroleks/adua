<?php

/*
Component: AdmissionLetterService
File Path: app/Services/AdmissionLetterService.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Manages the issuance and preservation of official admission offer letters.

Responsibilities:
- Validate admission eligibility before issuing an offer.
- Generate unique official letter numbers.
- Build complete document content snapshots.
- Preserve historical offer versions.
- Supersede previous issued offers when a new version is created.
- Withdraw currently issued offers when admission outcomes change.
- Preserve withdrawal history and responsible officers.
- Maintain transactional consistency.

Architecture:
- AdmissionDecision
- AdmissionLetter
- Application
- Programme
- User

Status: Integration / Hardening
Version: 1.2
*/

namespace App\Services;

use App\Models\AdmissionDecision;
use App\Models\AdmissionLetter;
use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class AdmissionLetterService
{
    /*
    |--------------------------------------------------------------------------
    | Issue Official Admission Offer
    |--------------------------------------------------------------------------
    */

    /**
     * Issue a new official admission offer.
     *
     * Each issuance creates a new letter record and version.
     * Previous issued offers are preserved as superseded records.
     */
    public function issue(
        AdmissionDecision $decision,
        User $officer
    ): AdmissionLetter {
        return DB::transaction(function () use ($decision, $officer) {

            /*
             * Lock the admission decision to prevent concurrent
             * issuance of multiple active offers.
             */
            $lockedDecision = AdmissionDecision::query()
                ->whereKey($decision->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedDecision->load([
                'application.user',
                'application.programme.department',
                'application.programme.faculty',
                'admissionOfficer',
            ]);

            $application = $lockedDecision->application;

            if (! $application) {
                throw new RuntimeException(
                    'Cannot issue an admission offer because the application record is missing.'
                );
            }

            if (! $lockedDecision->isAdmitted()) {
                throw new RuntimeException(
                    'An official admission offer can only be issued to an admitted applicant.'
                );
            }

            if (
                $application->application_status
                !== Application::STATUS_APPROVED
            ) {
                throw new RuntimeException(
                    'The application must be approved before an admission offer can be issued.'
                );
            }

            if (! $application->user) {
                throw new RuntimeException(
                    'Cannot issue an admission offer because the applicant record is missing.'
                );
            }

            if (! $application->programme) {
                throw new RuntimeException(
                    'Cannot issue an admission offer because the programme record is missing.'
                );
            }

            /*
             * Retrieve previous official offer letters only.
             *
             * Other letter types, such as conditional or rejection
             * letters, must not be superseded by this operation.
             */
            $existingLetters = AdmissionLetter::query()
                ->where(
                    'admission_decision_id',
                    $lockedDecision->id
                )
                ->where(
                    'type',
                    AdmissionLetter::TYPE_OFFER
                )
                ->lockForUpdate()
                ->orderByDesc('version')
                ->get();

            $nextVersion = ((int) $existingLetters->max('version')) + 1;

            /*
             * Preserve previous offers while marking currently
             * issued versions as superseded.
             *
             * Withdrawn and previously superseded records remain
             * unchanged.
             */
            $existingLetters
                ->where('status', AdmissionLetter::STATUS_ISSUED)
                ->each(function (AdmissionLetter $letter) {
                    $letter->update([
                        'status' => AdmissionLetter::STATUS_SUPERSEDED,
                        'superseded_at' => now(),
                    ]);
                });

            $issuedAt = now();

            $letterNumber = $this->generateLetterNumber();

            $snapshot = $this->buildContentSnapshot(
                decision: $lockedDecision,
                application: $application,
                officer: $officer,
                issuedAt: $issuedAt,
                letterNumber: $letterNumber,
                version: $nextVersion,
            );

            return AdmissionLetter::create([
                // Original admission letter fields
                'application_id' => $application->id,
                'admission_decision_id' => $lockedDecision->id,
                'letter_number' => $letterNumber,
                'type' => AdmissionLetter::TYPE_OFFER,
                'issued_by' => $officer->id,
                'issued_at' => $issuedAt,
                'published_at' => $issuedAt,
                'file_path' => null,

                // Offer lifecycle fields
                'version' => $nextVersion,
                'status' => AdmissionLetter::STATUS_ISSUED,
                'content_snapshot' => $snapshot,
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Withdraw Current Official Offer
    |--------------------------------------------------------------------------
    */

    /**
     * Withdraw currently issued official admission offers.
     *
     * The original letter number, issue date and content snapshot
     * are preserved. Only the lifecycle status and withdrawal
     * information are updated.
     *
     * Previously withdrawn and superseded letters remain untouched.
     *
     * @return int Number of offer letters withdrawn.
     */
    public function withdrawCurrentOffer(
        AdmissionDecision $decision,
        User $officer,
        string $reason
    ): int {
        return DB::transaction(function () use (
            $decision,
            $officer,
            $reason
        ) {
            /*
             * Lock the decision to serialize withdrawal operations
             * against other offer lifecycle operations.
             */
            $lockedDecision = AdmissionDecision::query()
                ->whereKey($decision->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * Only currently issued official admission offers
             * belonging to this admission decision are eligible.
             */
            $letters = AdmissionLetter::query()
                ->where(
                    'admission_decision_id',
                    $lockedDecision->id
                )
                ->where(
                    'type',
                    AdmissionLetter::TYPE_OFFER
                )
                ->where(
                    'status',
                    AdmissionLetter::STATUS_ISSUED
                )
                ->lockForUpdate()
                ->get();

            if ($letters->isEmpty()) {
                return 0;
            }

            $withdrawnAt = now();

            foreach ($letters as $letter) {
                $letter->update([
                    'status' => AdmissionLetter::STATUS_WITHDRAWN,
                    'withdrawn_at' => $withdrawnAt,
                    'withdrawn_by' => $officer->id,
                    'withdrawal_reason' => $reason,
                ]);
            }

            return $letters->count();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Official Letter Number
    |--------------------------------------------------------------------------
    */

    /**
     * Generate a unique official admission letter number.
     *
     * Example:
     * ADMIT-2026-A8K4P2QX
     */
    protected function generateLetterNumber(): string
    {
        do {
            $letterNumber = 'ADMIT-'
                . now()->format('Y')
                . '-'
                . Str::upper(Str::random(8));

        } while (
            AdmissionLetter::query()
                ->where('letter_number', $letterNumber)
                ->exists()
        );

        return $letterNumber;
    }

    /*
    |--------------------------------------------------------------------------
    | Build Immutable Content Snapshot
    |--------------------------------------------------------------------------
    */

    /**
     * Build the official offer content from the records available
     * at the time the offer is issued.
     */
    protected function buildContentSnapshot(
        AdmissionDecision $decision,
        Application $application,
        User $officer,
        $issuedAt,
        string $letterNumber,
        int $version
    ): array {
        $programme = $application->programme;

        /*
         * The officer who recorded the institutional admission
         * outcome is the preferred signatory.
         *
         * Fall back to the officer issuing the letter.
         */
        $signatory = $decision->admissionOfficer ?? $officer;

        return [
            'document' => [
                'title' => 'Official Admission Offer',
                'letter_number' => $letterNumber,
                'version' => $version,
                'issued_at' => $issuedAt->toIso8601String(),
                'issued_date' => $issuedAt->format('d F Y'),
            ],

            'institution' => [
                'name' => setting('institution.name'),
                'short_name' => setting('institution.short_name'),
                'logo' => setting('institution.logo'),
                'address' => setting('institution.address'),
                'website' => setting('institution.website'),
                'email' => setting('institution.email'),
                'phone' => setting('institution.phone'),
            ],

            'applicant' => [
                'name' => $application->user->name,
                'email' => $application->user->email,
            ],

            'application' => [
                'application_number' => $application->application_number,
            ],

            'admission' => [
                'status' => $decision->admission_status,
                'remarks' => $decision->admission_remarks,
                'decided_at' => $decision->admission_decided_at
                    ?->toIso8601String(),
            ],

            'programme' => [
                'name' => $programme->name,
                'code' => $programme->code,
                'degree_type' => $programme->degree_type,
                'duration' => $programme->duration,
                'department' => $programme->department?->name,
                'faculty' => $programme->faculty?->name,
            ],

            'signatory' => [
                'name' => $signatory->name,
                'designation' => 'Admissions Officer',
            ],
        ];
    }
}
