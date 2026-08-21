<?php

/**
 * Component: ApplicationList Controller
 * File Path: app/Http/Controllers/ApplicationListController.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Provides applicant-facing routes for:
 * - Listing all applications (overview across cycles/programmes)
 * - Viewing a single application dossier in detail
 *
 * Status: ✅ Production Ready
 * Version: 1.0
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class ApplicationListController extends Controller
{
    /**
     * Display all applications for the authenticated student.
     *
     * Route name: applications.index
     * URL: /applications
     */
    public function index(Request $request)
    {
        // Load all applications for the logged-in user
        $applications = $request->user()
            ->applications()
            ->with(['programme', 'decision.officer', 'payment'])
            ->latest()
            ->get();

        return view('applications.index', compact('applications'));
    }

    /**
     * Display the full dossier for a single application.
     *
     * Route name: applications.show
     * URL: /applications/{application}
     */
    public function show(Application $application, Request $request)
    {
        // Ensure the logged-in user owns this application
        if ($application->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized access to application dossier.');
        }

        // Eager load relations for dossier view
        $application->load([
            'programme',
            'documents.documentType',
            'decision.officer',
            'payment',
        ]);

        return view('applications.my-application', compact('application'));
    }
}
