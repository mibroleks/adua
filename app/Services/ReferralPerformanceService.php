<?php

/**
 * Component: Referral Performance Service
 * File Path: app/Services/ReferralPerformanceService.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Central reporting service for referral recruitment performance.
 *
 * Responsibilities:
 * - Report application and unique applicant counts per referral source.
 * - Report application submission, payment and admission outcomes.
 * - Calculate application approval and applicant admission conversion rates.
 * - Identify applicants who have submitted multiple applications.
 * - Include inactive referral partners for historical reporting.
 * - Include a Direct / Organic source for applications without a referral partner.
 * - Respect the filters applied to the supplied application query.
 *
 * Important:
 * - This service is read-only.
 * - It does not modify referral attribution or application records.
 * - Application fee performance uses applications.payment_status.
 * - It does not aggregate unrelated payment transactions.
 *
 * Version: 1.1 (Eloquent aggregate attribute correction)
 */

namespace App\Services;

use App\Models\Application;
use App\Models\ReferralPartner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReferralPerformanceService
{
    /**
     * Return referral performance for an application query.
     *
     * The supplied query should be the already-filtered Application query
     * from the dashboard or Filament table.
     *
     * @return array{
     *     sources: array<int, array<string, mixed>>,
     *     totals: array<string, int|float>
     * }
     */
    public function getPerformance(Builder $applicationsQuery): array
    {
        $aggregates = $this->getSourceAggregates($applicationsQuery);

        $multipleApplicationCounts = $this->getMultipleApplicationCounts(
            $applicationsQuery
        );

        $partners = ReferralPartner::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'is_active']);

        $sources = [];

        foreach ($partners as $partner) {
            $partnerId = (int) $partner->id;

            $aggregate = $aggregates->get($partnerId, []);

            $sources[] = $this->formatSource(
                sourceType: 'partner',
                referralPartnerId: $partnerId,
                name: $partner->name,
                code: $partner->code,
                isActive: (bool) $partner->is_active,
                aggregate: $aggregate,
                multipleApplicationApplicants: (int) $multipleApplicationCounts->get(
                    $partnerId,
                    0
                ),
            );
        }

        $directAggregate = $aggregates->get('direct', []);

        $sources[] = $this->formatSource(
            sourceType: 'direct',
            referralPartnerId: null,
            name: 'Direct / Organic',
            code: null,
            isActive: true,
            aggregate: $directAggregate,
            multipleApplicationApplicants: (int) $multipleApplicationCounts->get(
                'direct',
                0
            ),
        );

        return [
            'sources' => $sources,
            'totals' => $this->calculateTotals($sources),
        ];
    }

    /**
     * Aggregate application performance by referral source.
     *
     * IMPORTANT:
     * Eloquent get() returns hydrated Application models.
     * Use getAttributes() to retrieve the selected aggregate
     * columns as a normal attribute array.
     */
    protected function getSourceAggregates(
        Builder $applicationsQuery
    ): Collection {
        $query = clone $applicationsQuery;

        /*
         * Remove presentation-level query clauses while preserving
         * the original query's filters, scopes and joins.
         */
        $baseQuery = $query->getQuery();

        $baseQuery->columns = null;
        $baseQuery->orders = null;
        $baseQuery->groups = null;
        $baseQuery->havings = null;
        $baseQuery->limit = null;
        $baseQuery->offset = null;
        $baseQuery->distinct = false;

        $query
            ->selectRaw('
                applications.referral_partner_id,

                COUNT(*) AS total_applications,

                COUNT(DISTINCT applications.user_id)
                    AS unique_applicants,

                SUM(
                    CASE
                        WHEN applications.application_status = ?
                        THEN 1 ELSE 0
                    END
                ) AS draft_applications,

                SUM(
                    CASE
                        WHEN applications.application_status <> ?
                        THEN 1 ELSE 0
                    END
                ) AS submitted_applications,

                SUM(
                    CASE
                        WHEN applications.payment_status = ?
                        THEN 1 ELSE 0
                    END
                ) AS paid_applications,

                SUM(
                    CASE
                        WHEN applications.payment_status = ?
                        THEN 1 ELSE 0
                    END
                ) AS pending_payment_applications,

                SUM(
                    CASE
                        WHEN applications.payment_status = ?
                        THEN 1 ELSE 0
                    END
                ) AS failed_payment_applications,

                SUM(
                    CASE
                        WHEN applications.application_status = ?
                        THEN 1 ELSE 0
                    END
                ) AS under_review_applications,

                SUM(
                    CASE
                        WHEN applications.application_status = ?
                        THEN 1 ELSE 0
                    END
                ) AS correction_required_applications,

                SUM(
                    CASE
                        WHEN applications.application_status = ?
                        THEN 1 ELSE 0
                    END
                ) AS approved_applications,

                COUNT(
                    DISTINCT CASE
                        WHEN applications.application_status = ?
                        THEN applications.user_id
                        ELSE NULL
                    END
                ) AS approved_applicants,

                SUM(
                    CASE
                        WHEN applications.application_status = ?
                        THEN 1 ELSE 0
                    END
                ) AS rejected_applications
            ', [
                Application::STATUS_DRAFT,
                Application::STATUS_DRAFT,
                Application::PAYMENT_SUCCESS,
                Application::PAYMENT_PENDING,
                Application::PAYMENT_FAILED,
                Application::STATUS_UNDER_REVIEW,
                Application::STATUS_CORRECTION_REQUIRED,
                Application::STATUS_APPROVED,
                Application::STATUS_APPROVED,
                Application::STATUS_REJECTED,
            ])
            ->groupBy('applications.referral_partner_id');

        return $query
            ->get()
            ->mapWithKeys(function (Application $row): array {

                /*
                 * Use Eloquent's actual selected attributes.
                 *
                 * Casting an Eloquent model directly to an array
                 * exposes its internal structure, not a flat list
                 * of the selected database columns.
                 */
                $attributes = $row->getAttributes();

                $key = $attributes['referral_partner_id'] === null
                    ? 'direct'
                    : (int) $attributes['referral_partner_id'];

                return [
                    $key => $attributes,
                ];
            });
    }

    /**
     * Count applicants with more than one application per referral source.
     */
    protected function getMultipleApplicationCounts(
        Builder $applicationsQuery
    ): Collection {
        $query = clone $applicationsQuery;

        $baseQuery = $query->getQuery();

        $baseQuery->columns = null;
        $baseQuery->orders = null;
        $baseQuery->groups = null;
        $baseQuery->havings = null;
        $baseQuery->limit = null;
        $baseQuery->offset = null;
        $baseQuery->distinct = false;

        $applicantsWithMultipleApplications = $query
            ->whereNotNull('applications.user_id')
            ->selectRaw('
                applications.referral_partner_id,
                applications.user_id,
                COUNT(*) AS application_count
            ')
            ->groupBy(
                'applications.referral_partner_id',
                'applications.user_id'
            )
            ->havingRaw('COUNT(*) > 1')
            ->get();

        return $applicantsWithMultipleApplications
            ->groupBy(function ($row) {

                return $row->referral_partner_id === null
                    ? 'direct'
                    : (int) $row->referral_partner_id;

            })
            ->map(fn (Collection $applicants) => $applicants->count());
    }

    /**
     * Format one referral source consistently for all reporting screens.
     */
    protected function formatSource(
        string $sourceType,
        ?int $referralPartnerId,
        string $name,
        ?string $code,
        bool $isActive,
        array $aggregate,
        int $multipleApplicationApplicants,
    ): array {
        $totalApplications = (int) (
            $aggregate['total_applications'] ?? 0
        );

        $uniqueApplicants = (int) (
            $aggregate['unique_applicants'] ?? 0
        );

        $submittedApplications = (int) (
            $aggregate['submitted_applications'] ?? 0
        );

        $approvedApplications = (int) (
            $aggregate['approved_applications'] ?? 0
        );

        $approvedApplicants = (int) (
            $aggregate['approved_applicants'] ?? 0
        );

        return [
            'source_type' => $sourceType,
            'referral_partner_id' => $referralPartnerId,
            'name' => $name,
            'code' => $code,
            'is_active' => $isActive,

            'total_applications' => $totalApplications,
            'unique_applicants' => $uniqueApplicants,

            'draft_applications' => (int) (
                $aggregate['draft_applications'] ?? 0
            ),

            'submitted_applications' => $submittedApplications,

            'paid_applications' => (int) (
                $aggregate['paid_applications'] ?? 0
            ),

            'pending_payment_applications' => (int) (
                $aggregate['pending_payment_applications'] ?? 0
            ),

            'failed_payment_applications' => (int) (
                $aggregate['failed_payment_applications'] ?? 0
            ),

            'under_review_applications' => (int) (
                $aggregate['under_review_applications'] ?? 0
            ),

            'correction_required_applications' => (int) (
                $aggregate['correction_required_applications'] ?? 0
            ),

            'approved_applications' => $approvedApplications,
            'approved_applicants' => $approvedApplicants,

            'rejected_applications' => (int) (
                $aggregate['rejected_applications'] ?? 0
            ),

            'multiple_application_applicants' => $multipleApplicationApplicants,

            'application_approval_rate' => $this->percentage(
                $approvedApplications,
                $submittedApplications
            ),

            'applicant_admission_rate' => $this->percentage(
                $approvedApplicants,
                $uniqueApplicants
            ),
        ];
    }

    /**
     * Calculate overall totals across all referral sources.
     */
    protected function calculateTotals(array $sources): array
    {
        $fields = [
            'total_applications',
            'unique_applicants',
            'draft_applications',
            'submitted_applications',
            'paid_applications',
            'pending_payment_applications',
            'failed_payment_applications',
            'under_review_applications',
            'correction_required_applications',
            'approved_applications',
            'approved_applicants',
            'rejected_applications',
            'multiple_application_applicants',
        ];

        $totals = [];

        foreach ($fields as $field) {
            $totals[$field] = array_sum(
                array_column($sources, $field)
            );
        }

        $totals['application_approval_rate'] = $this->percentage(
            $totals['approved_applications'],
            $totals['submitted_applications']
        );

        $totals['applicant_admission_rate'] = $this->percentage(
            $totals['approved_applicants'],
            $totals['unique_applicants']
        );

        return $totals;
    }

    /**
     * Calculate a percentage safely.
     */
    protected function percentage(int $numerator, int $denominator): float
    {
        if ($denominator <= 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 1);
    }
}
