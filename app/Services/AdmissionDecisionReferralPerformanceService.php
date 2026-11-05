<?php

/*
Component: Admission Decision Referral Performance Service
File Path: app/Services/AdmissionDecisionReferralPerformanceService.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Calculates referral-source performance using institutional
admission decision records.

Responsibilities:
- Aggregate admission decisions by original referral source.
- Count decision records and unique applicants.
- Count application-processing approvals and rejections.
- Count actual institutional admission outcomes.
- Calculate actual admitted-applicant conversion rates.
- Include inactive referral partners.
- Include referral partners with zero decision records.
- Report Direct / Organic applications separately.

Architecture:
- Referral attribution remains owned by Application.
- Institutional admission is determined by admission_status.
- Application approval is not treated as admission.
- Reporting is read-only.
- The supplied Admission Decisions query is preserved.
- Existing Application referral reporting is not modified.

Status: Implementation
Version: 1.0
*/

namespace App\Services;

use App\Models\ReferralPartner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AdmissionDecisionReferralPerformanceService
{
    /**
     * Calculate referral-source admission decision performance.
     *
     * The supplied query is expected to be the Admission Decisions
     * table query, including any active table filters.
     *
     * @return array{
     *     sources: array<int, array<string, mixed>>,
     *     totals: array<string, int|float>
     * }
     */
    public function getPerformance(Builder $admissionDecisionsQuery): array
    {
        /*
        |--------------------------------------------------------------------------
        | Initialise report totals
        |--------------------------------------------------------------------------
        */

        $totals = [
            'total_decisions' => 0,
            'unique_applicants' => 0,
            'approved_applications' => 0,
            'rejected_applications' => 0,
            'pending_admission_decisions' => 0,
            'admitted_decisions' => 0,
            'not_admitted_decisions' => 0,
            'waitlisted_decisions' => 0,
            'deferred_decisions' => 0,
            'admitted_applicants' => 0,
            'admission_conversion_rate' => 0.0,
        ];

        /*
        |--------------------------------------------------------------------------
        | Load the filtered decision records
        |--------------------------------------------------------------------------
        |
        | Only the original Application fields required for reporting
        | are loaded.
        |
        | The query is cloned so this service cannot modify the
        | table's active query or its filters.
        |
        */

        $decisions = (clone $admissionDecisionsQuery)
            ->with([
                'application:id,user_id,referral_partner_id',
            ])
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Load the referral partner roster
        |--------------------------------------------------------------------------
        |
        | Do not filter by is_active.
        |
        | Inactive partners and partners with no matching decision
        | records must remain visible in the report.
        |
        | Existing model global scopes are respected.
        |
        */

        $partners = ReferralPartner::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'is_active',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Initialise source buckets
        |--------------------------------------------------------------------------
        */

        $sourceBuckets = [];

        foreach ($partners as $partner) {
            $sourceBuckets[(string) $partner->id] = [
                'source_type' => 'partner',
                'referral_partner_id' => $partner->id,
                'name' => $partner->name,
                'code' => $partner->code,
                'is_active' => (bool) $partner->is_active,

                'total_decisions' => 0,
                'unique_applicants' => 0,

                'approved_applications' => 0,
                'rejected_applications' => 0,

                'pending_admission_decisions' => 0,
                'admitted_decisions' => 0,
                'not_admitted_decisions' => 0,
                'waitlisted_decisions' => 0,
                'deferred_decisions' => 0,

                'admitted_applicants' => 0,
                'admission_conversion_rate' => 0.0,

                '_applicant_ids' => [],
                '_admitted_applicant_ids' => [],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Direct / Organic source
        |--------------------------------------------------------------------------
        |
        | This source is always included, even when its count is zero.
        |
        */

        $sourceBuckets['direct'] = [
            'source_type' => 'direct',
            'referral_partner_id' => null,
            'name' => 'Direct / Organic',
            'code' => null,
            'is_active' => true,

            'total_decisions' => 0,
            'unique_applicants' => 0,

            'approved_applications' => 0,
            'rejected_applications' => 0,

            'pending_admission_decisions' => 0,
            'admitted_decisions' => 0,
            'not_admitted_decisions' => 0,
            'waitlisted_decisions' => 0,
            'deferred_decisions' => 0,

            'admitted_applicants' => 0,
            'admission_conversion_rate' => 0.0,

            '_applicant_ids' => [],
            '_admitted_applicant_ids' => [],
        ];

        /*
        |--------------------------------------------------------------------------
        | Aggregate filtered admission decisions
        |--------------------------------------------------------------------------
        */

        foreach ($decisions as $decision) {
            $application = $decision->application;

            /*
             * A decision without its original application cannot
             * be attributed to a referral source or applicant.
             */
            if (! $application) {
                continue;
            }

            $partnerId = $application->referral_partner_id;

            $sourceKey = $partnerId !== null
                ? (string) $partnerId
                : 'direct';

            /*
             * If an application references a partner that is no
             * longer present in the roster, retain its attribution
             * in a separate historical source bucket.
             */
            if (! isset($sourceBuckets[$sourceKey])) {
                $sourceBuckets[$sourceKey] = [
                    'source_type' => 'partner',
                    'referral_partner_id' => $partnerId,
                    'name' => 'Referral Partner #' . $partnerId,
                    'code' => null,
                    'is_active' => false,

                    'total_decisions' => 0,
                    'unique_applicants' => 0,

                    'approved_applications' => 0,
                    'rejected_applications' => 0,

                    'pending_admission_decisions' => 0,
                    'admitted_decisions' => 0,
                    'not_admitted_decisions' => 0,
                    'waitlisted_decisions' => 0,
                    'deferred_decisions' => 0,

                    'admitted_applicants' => 0,
                    'admission_conversion_rate' => 0.0,

                    '_applicant_ids' => [],
                    '_admitted_applicant_ids' => [],
                ];
            }

            $source =& $sourceBuckets[$sourceKey];

            $source['total_decisions']++;

            /*
             * Application-processing decision.
             *
             * This is intentionally separate from the
             * institutional admission outcome below.
             */

            if ($decision->decision === 'APPROVED') {
                $source['approved_applications']++;
            }

            if ($decision->decision === 'REJECTED') {
                $source['rejected_applications']++;
            }

            /*
             * Institutional admission outcome.
             */

            switch ($decision->admission_status) {
                case 'PENDING':
                    $source['pending_admission_decisions']++;
                    break;

                case 'ADMITTED':
                    $source['admitted_decisions']++;
                    break;

                case 'NOT_ADMITTED':
                    $source['not_admitted_decisions']++;
                    break;

                case 'WAITLISTED':
                    $source['waitlisted_decisions']++;
                    break;

                case 'DEFERRED':
                    $source['deferred_decisions']++;
                    break;
            }

            /*
             * Unique applicant attribution.
             *
             * user_id is used instead of decision ID so an
             * applicant is not counted multiple times.
             */

            $applicantId = $application->user_id;

            if ($applicantId !== null) {
                $applicantKey = (string) $applicantId;

                $source['_applicant_ids'][$applicantKey] = true;

                if ($decision->admission_status === 'ADMITTED') {
                    $source['_admitted_applicant_ids'][$applicantKey] = true;
                }
            }

            unset($source);
        }

        /*
        |--------------------------------------------------------------------------
        | Finalise source metrics
        |--------------------------------------------------------------------------
        */

        foreach ($sourceBuckets as &$source) {
            $source['unique_applicants'] = count(
                $source['_applicant_ids']
            );

            $source['admitted_applicants'] = count(
                $source['_admitted_applicant_ids']
            );

            $source['admission_conversion_rate'] =
                $source['unique_applicants'] > 0
                    ? round(
                        (
                            $source['admitted_applicants']
                            / $source['unique_applicants']
                        ) * 100,
                        2
                    )
                    : 0.0;

            unset(
                $source['_applicant_ids'],
                $source['_admitted_applicant_ids']
            );
        }

        unset($source);

        /*
        |--------------------------------------------------------------------------
        | Calculate overall totals
        |--------------------------------------------------------------------------
        */

        foreach ($sourceBuckets as $source) {
            $totals['total_decisions'] += $source['total_decisions'];

            $totals['approved_applications'] +=
                $source['approved_applications'];

            $totals['rejected_applications'] +=
                $source['rejected_applications'];

            $totals['pending_admission_decisions'] +=
                $source['pending_admission_decisions'];

            $totals['admitted_decisions'] +=
                $source['admitted_decisions'];

            $totals['not_admitted_decisions'] +=
                $source['not_admitted_decisions'];

            $totals['waitlisted_decisions'] +=
                $source['waitlisted_decisions'];

            $totals['deferred_decisions'] +=
                $source['deferred_decisions'];
        }

        /*
         * Calculate unique applicants across all sources.
         *
         * This is calculated from the filtered decision collection,
         * rather than adding source-level unique counts, because
         * source-level totals should not be assumed to be a global
         * distinct count.
         */

        $uniqueApplicantIds = [];
        $admittedApplicantIds = [];

        foreach ($decisions as $decision) {
            $application = $decision->application;

            if (! $application || $application->user_id === null) {
                continue;
            }

            $applicantKey = (string) $application->user_id;

            $uniqueApplicantIds[$applicantKey] = true;

            if ($decision->admission_status === 'ADMITTED') {
                $admittedApplicantIds[$applicantKey] = true;
            }
        }

        $totals['unique_applicants'] = count($uniqueApplicantIds);

        $totals['admitted_applicants'] = count($admittedApplicantIds);

        $totals['admission_conversion_rate'] =
            $totals['unique_applicants'] > 0
                ? round(
                    (
                        $totals['admitted_applicants']
                        / $totals['unique_applicants']
                    ) * 100,
                    2
                )
                : 0.0;

        /*
        |--------------------------------------------------------------------------
        | Prepare ordered source output
        |--------------------------------------------------------------------------
        |
        | Referral partners are alphabetically ordered.
        | Direct / Organic is displayed after registered partners.
        |
        */

        $sources = array_values($sourceBuckets);

        usort(
            $sources,
            function (array $a, array $b): int {
                if ($a['source_type'] === 'direct') {
                    return 1;
                }

                if ($b['source_type'] === 'direct') {
                    return -1;
                }

                return strcasecmp($a['name'], $b['name']);
            }
        );

        return [
            'sources' => $sources,
            'totals' => $totals,
        ];
    }
}
