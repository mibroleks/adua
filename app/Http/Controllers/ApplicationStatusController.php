<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApplicationStatusController extends Controller
{
    /**
     * Display the current status of an application with its audit trail.
     *
     * Route name: application.status
     * URL: /application/{application}/status
     */
    public function show(Application $application)
    {
        // Ensure the authenticated user owns this application
        Gate::authorize('view', $application);

        // Load status history (audit trail)
        $history = $application->statusHistories()
            ->latest('changed_at')
            ->get();

        // Render the applicant-facing status view
        return view('status', compact('application', 'history'));
    }

    /**
     * Display the applicant’s overall progress through the admission journey.
     *
     * Route name: applications.progress
     * URL: /applications/progress
     */
    public function progress(Request $request)
    {
        $application = Application::with(['payment', 'documents', 'decision.officer'])
            ->where('user_id', $request->user()->id)
            ->first();

        return view('applications.progress', compact('application'));
    }
}
