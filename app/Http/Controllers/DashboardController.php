<?php

/**
 * Component: Dashboard Controller
 * File Path: app/Http/Controllers/DashboardController.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Shows the student dashboard with their application details.
 * Loads programme, payment, documents, and admission decision relations.
 * Provides summary metrics (progress, payment status, document count, decision).
 * Ensures the authenticated student only sees their own application.
 *
 * Status: 🚦 Integration / Hardening
 * Version: 2.1 (fixed domain consistency issues)
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class DashboardController extends Controller
{
    /**
     * Display the student dashboard.
     */
    public function index(Request $request)
    {
        $application = Application::with([
                'programme.department.faculty', // full academic hierarchy
                'payment',
                'documents.documentType',
                'decision.officer',
                'statusHistories.officer',      // audit trail
            ])
            ->where('user_id', $request->user()->id)
            ->first();

        // Build summary metrics if application exists
        $summary = null;
        if ($application) {
            $summary = [
                'programme'       => $application->programme?->name,
                'payment_status'  => $application->payment?->status ?? 'unpaid',
                'documents_count' => $application->documents->count(),
                'decision'        => $application->decision?->decision ?? 'pending',
                'progress_stage'  => $application->status ?? 'draft', // canonical status
                'status_label'    => $application->statusLabel(),     // human-friendly label
                'payment_label'   => $application->paymentLabel(),    // human-friendly payment label
            ];
        }

        return view('dashboard', [
            'application' => $application, // may be null if not yet applied
            'summary'     => $summary,     // null if no application yet
        ]);
    }
}
