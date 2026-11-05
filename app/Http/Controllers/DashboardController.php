<?php

/**
 * Component: Dashboard Controller
 * File Path: app/Http/Controllers/DashboardController.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Displays the applicant dashboard across all applications
 * belonging to the authenticated applicant.
 *
 * Responsibilities:
 * - Retrieve all applications belonging to the authenticated applicant.
 * - Load programme, payment, documents and admission decision relationships.
 * - Retrieve currently issued official admission offers.
 * - Provide applicant-wide dashboard summary metrics.
 * - Keep application-specific information associated with its application.
 * - Prevent applicants from accessing another applicant's records.
 *
 * Architecture:
 * - Application
 * - AdmissionDecision
 * - AdmissionLetter
 * - Payment
 * - ApplicationDocument
 * - User
 *
 * Status: Integration / Hardening
 * Version: 3.1 (Multi-Application Dashboard Hardening)
 */

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\AdmissionLetter;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the authenticated applicant's dashboard.
     */
    public function index(Request $request)
    {
        $applicantId = $request->user()->id;

        /*
        |--------------------------------------------------------------------------
        | Applicant Applications
        |--------------------------------------------------------------------------
        |
        | Retrieve every application belonging to the authenticated applicant.
        |
        | Each application's payment, documents, programme and decision
        | remain associated with that individual application.
        |
        */

        $applications = Application::query()
            ->where('user_id', $applicantId)
            ->with([
                'programme.department.faculty',
                'payment',
                'documents.documentType',
                'decision.officer',
                'statusHistories.officer',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Currently Issued Official Admission Offers
        |--------------------------------------------------------------------------
        |
        | Only current issued OFFER letters belonging to this applicant's
        | applications are included.
        |
        | The application's current institutional admission outcome
        | must be ADMITTED.
        |
        | Withdrawn and superseded offers are excluded.
        |
        */

        $applicationIds = $applications->pluck('id');

        $issuedOffers = AdmissionLetter::query()
            ->whereIn('application_id', $applicationIds)
            ->where('type', AdmissionLetter::TYPE_OFFER)
            ->where('status', AdmissionLetter::STATUS_ISSUED)
            ->whereHas('application', function ($query) use ($applicantId) {
                $query->where('user_id', $applicantId);
            })
            ->whereHas('application.decision', function ($query) {
                $query->where('admission_status', 'ADMITTED');
            })
            ->with([
                'application.programme',
            ])
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->get()
            ->unique('application_id')
            ->keyBy('application_id');

        /*
        |--------------------------------------------------------------------------
        | Applicant-Wide Dashboard Summary
        |--------------------------------------------------------------------------
        |
        | These metrics cover all applications belonging to the applicant.
        |
        | applications_with_payment counts applications with an associated
        | payment record. It does not imply that every payment is confirmed.
        |
        */

        $summary = [
            'applications_count' => $applications->count(),

            'official_offers_count' => $issuedOffers->count(),

            'applications_with_payment' => $applications
                ->filter(fn ($application) => $application->payment !== null)
                ->count(),

            'documents_count' => $applications
                ->sum(fn ($application) => $application->documents->count()),
        ];

        /*
        |--------------------------------------------------------------------------
        | Dashboard View
        |--------------------------------------------------------------------------
        */

        return view('dashboard', [
            'applications' => $applications,
            'issuedOffers' => $issuedOffers,
            'summary' => $summary,
        ]);
    }
}