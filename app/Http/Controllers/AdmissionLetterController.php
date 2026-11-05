<?php

/**
 * Component: AdmissionLetter Controller
 * File Path: app/Http/Controllers/AdmissionLetterController.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Manages applicant-facing application outcome letters and
 * official institutional admission offer letters.
 *
 * Responsibilities:
 * - Verify authenticated applicant ownership.
 * - Display application processing outcomes separately from admission offers.
 * - Restrict official offers to formally admitted applicants.
 * - List currently issued official offers belonging to the applicant.
 * - Retrieve the latest currently issued official offer.
 * - Render official offers from immutable content snapshots.
 * - Prevent access to withdrawn or superseded offers as current offers.
 *
 * Architecture:
 * - Application
 * - AdmissionDecision
 * - AdmissionLetter
 * - User
 *
 * Status: Integration / Hardening
 * Version: 3.2 (Independent Outcome Letter Access)
 */

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\AdmissionLetter;
use Illuminate\Http\Request;

class AdmissionLetterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Application Outcome Letter
    |--------------------------------------------------------------------------
    */

    /**
     * Display the application processing outcome letter.
     *
     * This document is separate from the official institutional
     * admission offer. Both documents remain independently accessible.
     *
     * Route name: admission.letter
     */
    public function show(Request $request, Application $application)
    {
        $this->ensureApplicantOwnsApplication(
            $request,
            $application
        );

        $application->load([
            'programme',
            'decision.officer',
            'user',
        ]);

        if (! $application->decision) {
            return redirect()
                ->route('dashboard')
                ->with(
                    'error',
                    'Application decision not available yet.'
                );
        }

        if ($application->decision->decision !== 'APPROVED') {
            return redirect()
                ->route('dashboard')
                ->with(
                    'error',
                    'Application outcome letter is only available for approved applications.'
                );
        }

        return view('admission_letter', [
            'application' => $application,
            'programme' => $application->programme,
            'decision' => $application->decision,
            'officer' => $application->decision->officer,
            'applicant' => $application->user,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Applicant Official Offers List
    |--------------------------------------------------------------------------
    */

    /**
     * Display all currently issued official admission offers
     * belonging to the authenticated applicant.
     *
     * Withdrawn and superseded offers are excluded.
     *
     * Route name: admission.offers.index
     */
    public function offers(Request $request)
    {
        $offers = AdmissionLetter::query()
            ->whereHas('application', function ($query) use ($request) {
                $query->where(
                    'user_id',
                    $request->user()->id
                );
            })
            ->where(
                'type',
                AdmissionLetter::TYPE_OFFER
            )
            ->where(
                'status',
                AdmissionLetter::STATUS_ISSUED
            )
            ->whereHas('application.decision', function ($query) {
                $query->where(
                    'admission_status',
                    'ADMITTED'
                );
            })
            ->with([
                'application.programme',
            ])
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->get();

        return view('admission_offers.index', [
            'offers' => $offers,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Individual Official Admission Offer
    |--------------------------------------------------------------------------
    */

    /**
     * Display the applicant's latest currently issued official offer.
     *
     * The document is rendered from its saved content snapshot.
     * Current application or programme changes must not silently
     * alter the contents of an already issued offer.
     *
     * Route name: admission.offer
     */
    public function officialOffer(
        Request $request,
        Application $application
    ) {
        $this->ensureApplicantOwnsApplication(
            $request,
            $application
        );

        $application->load('decision');

        if (! $application->decision) {
            return redirect()
                ->route('dashboard')
                ->with(
                    'error',
                    'Admission decision not available yet.'
                );
        }

        if (! $application->decision->isAdmitted()) {
            return redirect()
                ->route('dashboard')
                ->with(
                    'error',
                    'An official admission offer is not available because your application has not been formally admitted.'
                );
        }

        $letter = AdmissionLetter::query()
            ->where('application_id', $application->id)
            ->where(
                'admission_decision_id',
                $application->decision->id
            )
            ->where(
                'type',
                AdmissionLetter::TYPE_OFFER
            )
            ->where(
                'status',
                AdmissionLetter::STATUS_ISSUED
            )
            ->orderByDesc('version')
            ->first();

        if (! $letter) {
            return redirect()
                ->route('dashboard')
                ->with(
                    'error',
                    'Your admission outcome is recorded, but your official admission offer has not yet been issued. Please contact the Admissions Office.'
                );
        }

        $snapshot = $letter->content_snapshot;

        if (
            ! is_array($snapshot)
            || empty($snapshot['document'])
            || empty($snapshot['applicant'])
            || empty($snapshot['institution'])
            || empty($snapshot['programme'])
        ) {
            return redirect()
                ->route('dashboard')
                ->with(
                    'error',
                    'Your official admission offer requires administrative attention. Please contact the Admissions Office.'
                );
        }

        return view('official_admission_offer', [
            'letter' => $letter,
            'snapshot' => $snapshot,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Applicant Ownership
    |--------------------------------------------------------------------------
    */

    /**
     * Ensure the authenticated applicant owns the application.
     */
    protected function ensureApplicantOwnsApplication(
        Request $request,
        Application $application
    ): void {
        if (
            ! $request->user()
            || (int) $application->user_id !== (int) $request->user()->id
        ) {
            abort(
                403,
                'Unauthorized access to admission document.'
            );
        }
    }
}
