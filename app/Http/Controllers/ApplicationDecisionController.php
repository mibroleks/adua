<?php

/**
 * Component: ApplicationDecision Controller
 * File Path: app/Http/Controllers/ApplicationDecisionController.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Shows the logged-in applicant their admission decision.
 * Loads the decision relation with officer and remarks.
 * Ensures the authenticated student only sees their own application.
 *
 * Status: ✅ Production Ready
 * Version: 1.0
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class ApplicationDecisionController extends Controller
{
    /**
     * Display the applicant’s admission decision.
     *
     * Route name: applications.decision
     * URL: /applications/decision
     */
    public function show(Request $request)
    {
        // Load the applicant’s application with decision + officer
        $application = Application::with(['decision.officer'])
            ->where('user_id', $request->user()->id)
            ->first();

        // Render the applicant-facing decision view
        return view('applications.decision', compact('application'));
    }
}
