<?php

/**
 * Component: AdmissionDecision Controller
 * File Path: app/Http/Controllers/AdmissionDecisionController.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Handles officer admission decisions on applications.
 * Validates input, records decision, updates application status with audit trail
 * via AdmissionService.
 *
 * Status: ✅ Production Ready
 * Version: 2.0 (workflow delegated to AdmissionService, normalized field `decision`)
 */

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use App\Services\AdmissionService;

class AdmissionDecisionController extends Controller
{
    protected AdmissionService $admissionService;

    public function __construct(AdmissionService $admissionService)
    {
        $this->admissionService = $admissionService;
    }

    /**
     * Store a new admission decision for an application.
     *
     * Route name: application.decision.store
     */
    public function store(Request $request, Application $application)
    {
        $this->authorize('decide', $application);

        $validated = $request->validate([
            'decision' => 'required|in:APPROVED,REJECTED',
            'remarks'  => 'nullable|string|max:1000',
        ]);

        // Delegate workflow to AdmissionService
        $this->admissionService->recordDecision(
            $application,
            auth()->user(),
            $validated['decision'],
            $validated['remarks'] ?? null
        );

        return redirect()
            ->route('application.status', $application)
            ->with('success', "Application {$validated['decision']} successfully.");
    }
}
