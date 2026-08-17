<?php

/**
 * Component: AdmissionDecision Controller
 * File Path: app/Http/Controllers/AdmissionDecisionController.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Handles officer admission decisions on applications.
 * Validates input, records decision, updates application status with audit trail.
 *
 * Status: ✅ Production Ready
 * Version: 1.4
 */

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\AdmissionDecision;
use Illuminate\Http\Request;

class AdmissionDecisionController extends Controller
{
    /**
     * Store a new admission decision for an application.
     */
    public function store(Request $request, Application $application)
    {
        $this->authorize('decide', $application);

        $validated = $request->validate([
            'status'  => 'required|in:APPROVED,REJECTED',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // Create decision record
        AdmissionDecision::create([
            'application_id' => $application->id,
            'officer_id'     => auth()->id(),
            'status'         => $validated['status'],
            'remarks'        => $validated['remarks'] ?? null,
            'decided_at'     => now(),
        ]);

        // Update application status with audit trail
        $application->setStatus($validated['status'], auth()->id());

        return redirect()
            ->route('application.status', $application)
            ->with('success', "Application {$validated['status']} successfully.");
    }
}
