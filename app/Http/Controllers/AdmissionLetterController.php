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
 * Status: ✅ Production Ready
 * Version: 1.4
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class AdmissionLetterController extends Controller
{
    /**
     * Display the admission letter for an approved application.
     */
    public function show(Request $request, Application $application)
    {
        // Ensure the logged-in user owns this application
        if ($application->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized access to admission letter.');
        }

        // Load relations: programme + decision + officer
        $application->load(['programme', 'decision.officer']);

        // Ensure a decision exists
        if (! $application->decision) {
            return redirect()->route('dashboard')
                ->with('error', 'Admission decision not available yet.');
        }

        // Only show letter if status is APPROVED
        if ($application->decision->status !== 'APPROVED') {
            return redirect()->route('dashboard')
                ->with('error', 'Admission letter is only available for approved applications.');
        }

        return view('admission_letter', compact('application'));
    }
}
