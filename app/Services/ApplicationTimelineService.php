<?php

/*
Component: Application Timeline Service
File Path: app/Services/ApplicationTimelineService.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides a unified chronological timeline of an application’s lifecycle.
Merges status history, document history, payments, and decisions into one feed.
Used by applicant portal and officer dossier views.

Status: 🚦 Integration / Hardening
Version: 1.1 (aligned with remarks field)
*/

namespace App\Services;

use App\Models\Application;
use Illuminate\Support\Collection;

class ApplicationTimelineService
{
    /**
     * Build a unified timeline for an application.
     *
     * @param Application $application
     * @return Collection
     */
    public function build(Application $application): Collection
    {
        $events = collect();

        // Status history
        foreach ($application->statusHistory()->orderBy('created_at')->get() as $status) {
            $events->push([
                'type'       => 'status',
                'label'      => $status->new_status,   // ✅ use new_status field
                'details'    => $status->remarks,      // ✅ corrected from notes → remarks
                'created_at' => $status->created_at,
            ]);
        }

        // Document history
        foreach ($application->documentHistory()->orderBy('created_at')->get() as $doc) {
            $events->push([
                'type'       => 'document',
                'label'      => $doc->action,
                'details'    => $doc->remarks,         // ✅ use remarks for document actions
                'created_at' => $doc->created_at,
            ]);
        }

        // Payments
        foreach ($application->payments()->orderBy('created_at')->get() as $payment) {
            $events->push([
                'type'       => 'payment',
                'label'      => $payment->status,
                'details'    => $payment->formatted_amount,
                'created_at' => $payment->created_at,
            ]);
        }

        // Admission decisions
        foreach ($application->decisions()->orderBy('created_at')->get() as $decision) {
            $events->push([
                'type'       => 'decision',
                'label'      => $decision->decision,
                'details'    => $decision->remarks,    // ✅ corrected from notes → remarks
                'created_at' => $decision->created_at,
            ]);
        }

        // Sort all events chronologically
        return $events->sortBy('created_at')->values();
    }
}
