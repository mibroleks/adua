<?php

/**
 * Component: AdmissionLetter Controller
 * File Path: app/Http/Controllers/AdmissionLetterController.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Shows the admission letter for approved applications.
 * Ensures the authenticated student owns the application,
 * loads programme and decision relations, and returns the letter view.
 *
 * Status: 🚦 Integration / Hardening
 * Version: 2.1 (validated decision + improved view context)
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class AdmissionLetterController extends Controller
{
    /**
     * Display the admission letter for an approved application.
     *
     * Route name: admission.letter
     */
    public function show(Request $request, Application $application)
    {
        // Ensure the logged-in user owns this application
        if ($application->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized access to admission letter.');
        }

        // Load relations: programme + decision + officer
        $application->load(['programme', 'decision.officer', 'user']);

        // Ensure a decision exists
        if (! $application->decision) {
            return redirect()->route('dashboard')
                ->with('error', 'Admission decision not available yet.');
        }

        // Only show letter if decision is APPROVED
        if ($application->decision->decision !== 'APPROVED') {
            return redirect()->route('dashboard')
                ->with('error', 'Admission letter is only available for approved applications.');
        }

        // Pass additional context to the view for a richer letter
        return view('admission_letter', [
            'application' => $application,
            'programme'   => $application->programme,
            'decision'    => $application->decision,
            'officer'     => $application->decision->officer,
            'applicant'   => $application->user,
        ]);
    }
}
