<?php

/*
Component: Admission Service
File Path: app/Services/AdmissionService.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Handles the lifecycle of student applications.
Supports draft creation, submission, status transitions,
fee snapshotting, required-document validation,
and audit logging via ApplicationStatusHistory.

Status: ✅ Production Ready
Version: 2.2 (Payment gate + transactional submission)
*/

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationDocumentType;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AdmissionService
{
    /**
     * Create a new draft application for a user.
     *
     * The programme application fee is snapshotted immediately
     * so that later programme-fee changes do not alter this application.
     */
    public function createDraft(User $user, int $programmeId): Application
    {
        $programme = Programme::findOrFail($programmeId);

        return Application::create([
            'application_number' => $this->generateApplicationNumber(),
            'user_id'            => $user->id,
            'programme_id'       => $programme->id,
            'application_fee'    => $programme->application_fee,
            'application_status' => Application::STATUS_DRAFT,
            'payment_status'     => Application::PAYMENT_PENDING,
        ]);
    }

    /**
     * Determine whether the application has all required documents.
     *
     * This is the single source of truth for submission readiness.
     */
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
            $hasDocument = $application->documents()
                ->where('document_type_id', $docType->id)
                ->exists();

            if (! $hasDocument) {
                $missingDocs[] = $docType->name;
            }
        }

        return $missingDocs;
    }

    /**
     * Determine whether the application is ready for submission.
     */
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
     * Submit an application.
     *
     * Submission requires:
     * - application must be DRAFT
     * - payment must be SUCCESS
     * - all required documents must exist
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
                'Required documents are missing: '
                . implode(', ', $missingDocs)
            );
        }

        return DB::transaction(function () use ($application) {
            $application->refresh();

            if ($application->application_fee === null) {
                $application->snapshotFee();
            }

            $application->submitted_at = now();
            $application->save();

            $application->setApplicationStatus(
                Application::STATUS_SUBMITTED
            );

            return $application->fresh();
        });
    }

    /**
     * Change application status with audit logging.
     */
    public function changeStatus(
        Application $application,
        string $newStatus,
        ?int $officerId = null
    ): Application {
        $application->setApplicationStatus(
            $newStatus,
            $officerId
        );

        return $application->fresh();
    }

    /**
     * Approve an application.
     */
    public function approve(
        Application $application,
        ?int $officerId = null
    ): Application {
        return $this->changeStatus(
            $application,
            Application::STATUS_APPROVED,
            $officerId
        );
    }

    /**
     * Reject an application.
     */
    public function reject(
        Application $application,
        ?int $officerId = null
    ): Application {
        return $this->changeStatus(
            $application,
            Application::STATUS_REJECTED,
            $officerId
        );
    }

    /**
     * Generate a unique application number.
     */
    protected function generateApplicationNumber(): string
    {
        $year = now()->year;

        $count = Application::whereYear('created_at', $year)->count() + 1;

        return sprintf(
            'ADM-%d-%04d',
            $year,
            $count
        );
    }
}
