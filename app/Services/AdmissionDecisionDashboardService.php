<?php

/*
Component: Admission Decision Dashboard Service
File Path: app/Services/AdmissionDecisionDashboardService.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Read-only reporting service for institutional admission outcomes
on the Officer Admissions Dashboard.

Responsibilities:
- Institutional admission outcome summary.
- Admission outcome distribution.
- Referral partner admission performance.
- Recent institutional admission outcome activity.
- Consistent application-based dashboard filtering.
- Independent institutional admission outcome filtering.

Architecture:
- Reads from AdmissionDecision and its related Application records.
- Uses actual institutional admission outcomes.
- Keeps application processing decisions separate from admission outcomes.
- Preserves referral attribution through the Application relationship.
- Does not modify admission decisions or history.
- Does not issue admission letters.
- Respects existing Eloquent model scopes.

Filtering:
- Application period is based on the related application's creation date.
- Application status and payment filters apply to the related application.
- Programme and referral filters apply to the related application.
- Admission status applies directly to AdmissionDecision.
- Admission outcome filtering does not alter application reporting.

Status:
Dashboard Implementation
Version: 1.1
*/

namespace App\Services;

use App\Models\AdmissionDecision;
use App\Models\Application;
use App\Models\ReferralPartner;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AdmissionDecisionDashboardService
{
    /**
     * Supported institutional admission outcomes.
     */
    protected const ADMISSION_STATUSES = [
        AdmissionDecision::ADMISSION_STATUS_PENDING,
        AdmissionDecision::ADMISSION_STATUS_ADMITTED,
        AdmissionDecision::ADMISSION_STATUS_NOT_ADMITTED,
        AdmissionDecision::ADMISSION_STATUS_WAITLISTED,
        AdmissionDecision::ADMISSION_STATUS_DEFERRED,
    ];

    /**
     * Return the institutional admission outcome dataset.
     */
    public function getDashboardData(array $filters = []): array
    {
        $query = $this->filteredDecisions($filters);

        return [
            'summary' => $this->getSummary(
                clone $query
            ),

            'status_distribution' => $this->getStatusDistribution(
                clone $query
            ),

            'referral_performance' => $this->getReferralPerformance(
                clone $query
            ),

            'recent_activity' => $this->getRecentActivity(
                clone $query
            ),

            'filter_options' => [
                'admission_statuses' => $this->getAdmissionStatusOptions(),
            ],
        ];
    }

    /**
     * Build the admission decision query.
     *
     * Application filters are applied through the related
     * Application model so its existing model scopes remain active.
     *
     * Institutional admission status is applied directly to
     * the AdmissionDecision model.
     */
    protected function filteredDecisions(array $filters): Builder
    {
        $query = AdmissionDecision::query()
            ->whereHas('application', function (Builder $application) use ($filters) {
                $this->applyApplicationFilters($application, $filters);
            });

        $admissionStatus = $filters['admission_status'] ?? 'all';

        if (
            $admissionStatus !== 'all' &&
            in_array($admissionStatus, self::ADMISSION_STATUSES, true)
        ) {
            $query->where('admission_status', $admissionStatus);
        }

        return $query;
    }

    /**
     * Apply the shared admissions dashboard filters
     * to the related application.
     */
    protected function applyApplicationFilters(
        Builder $query,
        array $filters
    ): void {
        $this->applyDateFilter($query, $filters);

        $status = $filters['status'] ?? 'all';

        if ($status !== 'all' && $status !== '') {
            $query->where('application_status', $status);
        }

        $paymentStatus = $filters['payment_status'] ?? 'all';

        if ($paymentStatus !== 'all' && $paymentStatus !== '') {
            $query->where('payment_status', $paymentStatus);
        }

        $programmeId = $filters['programme_id'] ?? 'all';

        if (
            $programmeId !== 'all' &&
            $programmeId !== '' &&
            is_numeric($programmeId)
        ) {
            $query->where('programme_id', (int) $programmeId);
        }

        $referralSource = $filters['referral_source'] ?? 'all';

        if ($referralSource === 'direct') {
            $query->whereNull('referral_partner_id');
        } elseif (
            is_numeric($referralSource) &&
            (int) $referralSource > 0
        ) {
            $query->where('referral_partner_id', (int) $referralSource);
        }
    }

    /**
     * Apply the dashboard's application creation-date filter.
     */
    protected function applyDateFilter(
        Builder $query,
        array $filters
    ): void {
        switch ($filters['period'] ?? 'all_time') {
            case 'today':
                $query->whereDate('created_at', today());
                break;

            case 'this_week':
                $query->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]);
                break;

            case 'this_month':
                $query->whereBetween('created_at', [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ]);
                break;

            case 'this_year':
                $query->whereYear('created_at', now()->year);
                break;

            case 'custom':
                $dateFrom = $this->normaliseDate(
                    $filters['date_from'] ?? null
                );

                $dateTo = $this->normaliseDate(
                    $filters['date_to'] ?? null
                );

                if ($dateFrom) {
                    $query->where(
                        'created_at',
                        '>=',
                        Carbon::parse($dateFrom)->startOfDay()
                    );
                }

                if ($dateTo) {
                    $query->where(
                        'created_at',
                        '<=',
                        Carbon::parse($dateTo)->endOfDay()
                    );
                }

                break;
        }
    }

    /**
     * Safely normalise a custom date.
     */
    protected function normaliseDate(mixed $date): ?string
    {
        if (! is_string($date) || trim($date) === '') {
            return null;
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Institutional admission outcome summary.
     *
     * These figures come exclusively from admission_status.
     * They are not application processing decision counts.
     *
     * Unique applicants are counted through the Application
     * model so its existing Eloquent scopes remain active.
     */
    protected function getSummary(Builder $query): array
    {
        $total = (clone $query)->count();

        $statusCounts = (clone $query)
            ->selectRaw('admission_status, COUNT(*) as total')
            ->groupBy('admission_status')
            ->pluck('total', 'admission_status');

        $applicationIds = (clone $query)
            ->select('application_id')
            ->distinct();

        $uniqueApplicants = Application::query()
            ->whereIn('id', clone $applicationIds)
            ->whereNotNull('user_id')
            ->distinct()
            ->count('user_id');

        $admittedApplicationIds = (clone $query)
            ->where(
                'admission_status',
                AdmissionDecision::ADMISSION_STATUS_ADMITTED
            )
            ->select('application_id')
            ->distinct();

        $admittedApplicants = Application::query()
            ->whereIn('id', $admittedApplicationIds)
            ->whereNotNull('user_id')
            ->distinct()
            ->count('user_id');

        return [
            'total' => $total,

            'unique_applicants' => $uniqueApplicants,

            'pending' => (int) (
                $statusCounts[AdmissionDecision::ADMISSION_STATUS_PENDING] ?? 0
            ),

            'admitted' => (int) (
                $statusCounts[AdmissionDecision::ADMISSION_STATUS_ADMITTED] ?? 0
            ),

            'admitted_applicants' => $admittedApplicants,

            'not_admitted' => (int) (
                $statusCounts[AdmissionDecision::ADMISSION_STATUS_NOT_ADMITTED] ?? 0
            ),

            'waitlisted' => (int) (
                $statusCounts[AdmissionDecision::ADMISSION_STATUS_WAITLISTED] ?? 0
            ),

            'deferred' => (int) (
                $statusCounts[AdmissionDecision::ADMISSION_STATUS_DEFERRED] ?? 0
            ),
        ];
    }

    /**
     * Institutional admission outcome distribution.
     *
     * When a specific admission outcome is selected,
     * the distribution reflects that filtered dataset.
     */
    protected function getStatusDistribution(Builder $query): array
    {
        $statuses = self::ADMISSION_STATUSES;

        $counts = (clone $query)
            ->selectRaw('admission_status, COUNT(*) as total')
            ->groupBy('admission_status')
            ->pluck('total', 'admission_status');

        return collect($statuses)
            ->map(fn (string $status) => [
                'status' => $status,
                'label' => $this->statusLabel($status),
                'total' => (int) ($counts[$status] ?? 0),
            ])
            ->all();
    }

    /**
     * Return the available admission outcome filter options.
     */
    protected function getAdmissionStatusOptions(): array
    {
        return collect(self::ADMISSION_STATUSES)
            ->map(fn (string $status) => [
                'value' => $status,
                'label' => $this->statusLabel($status),
            ])
            ->all();
    }

    /**
     * Referral performance based on actual institutional outcomes.
     *
     * Referral attribution is obtained from:
     * AdmissionDecision -> Application -> referral_partner_id.
     *
     * The partner roster is retained so partners with zero
     * admission decisions can still appear in the report.
     */
    protected function getReferralPerformance(Builder $query): array
    {
        $decisions = (clone $query)
            ->with('application:id,user_id,referral_partner_id')
            ->get([
                'id',
                'application_id',
                'admission_status',
            ]);

        $grouped = $decisions->groupBy(
            fn (AdmissionDecision $decision) =>
                $decision->application?->referral_partner_id ?? 'direct'
        );

        $partners = ReferralPartner::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'is_active']);

        $sources = [];

        foreach ($partners as $partner) {
            $items = $grouped->get($partner->id, collect());

            $sources[] = $this->formatReferralSource(
                $items,
                [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'code' => $partner->code,
                    'source_type' => 'partner',
                    'is_active' => (bool) $partner->is_active,
                ]
            );
        }

        $directItems = $grouped->get('direct', collect());

        $sources[] = $this->formatReferralSource(
            $directItems,
            [
                'id' => null,
                'name' => 'Direct / Organic',
                'code' => null,
                'source_type' => 'direct',
                'is_active' => true,
            ]
        );

        return collect($sources)
            ->sortByDesc('total_decisions')
            ->values()
            ->all();
    }

    /**
     * Format admission outcomes for one referral source.
     */
    protected function formatReferralSource(
        $decisions,
        array $source
    ): array {
        $uniqueApplicants = $decisions
            ->map(fn ($decision) => $decision->application?->user_id)
            ->filter()
            ->unique()
            ->count();

        $admitted = $decisions->where(
            'admission_status',
            AdmissionDecision::ADMISSION_STATUS_ADMITTED
        );

        $admittedApplicants = $admitted
            ->map(fn ($decision) => $decision->application?->user_id)
            ->filter()
            ->unique()
            ->count();

        return array_merge($source, [
            'total_decisions' => $decisions->count(),

            'total' => $decisions->count(),

            'unique_applicants' => $uniqueApplicants,

            'pending' => $decisions->where(
                'admission_status',
                AdmissionDecision::ADMISSION_STATUS_PENDING
            )->count(),

            'admitted' => $admitted->count(),

            'admitted_applicants' => $admittedApplicants,

            'not_admitted' => $decisions->where(
                'admission_status',
                AdmissionDecision::ADMISSION_STATUS_NOT_ADMITTED
            )->count(),

            'waitlisted' => $decisions->where(
                'admission_status',
                AdmissionDecision::ADMISSION_STATUS_WAITLISTED
            )->count(),

            'deferred' => $decisions->where(
                'admission_status',
                AdmissionDecision::ADMISSION_STATUS_DEFERRED
            )->count(),

            'applicant_admission_rate' => $uniqueApplicants > 0
                ? round(
                    ($admittedApplicants / $uniqueApplicants) * 100,
                    1
                )
                : 0,
        ]);
    }

    /**
     * Recent institutional admission outcome changes.
     *
     * Reads the separate admission decision history.
     * Does not use application status history.
     */
    protected function getRecentActivity(Builder $query): array
    {
        $decisions = (clone $query)
            ->with([
                'application.user',
                'application.programme',
                'admissionHistories.officer',
            ])
            ->get();

        return $decisions
            ->flatMap(function (AdmissionDecision $decision) {
                return $decision->admissionHistories->map(
                    function ($history) use ($decision) {
                        $application = $decision->application;

                        return [
                            'type' => 'admission_outcome_changed',

                            'title' => 'Admission outcome updated',

                            'description' => sprintf(
                                '%s changed from %s to %s.',
                                $application?->application_number ?? 'Application',
                                $history->old_status
                                    ? $this->statusLabel($history->old_status)
                                    : 'Not set',
                                $this->statusLabel($history->new_status)
                            ),

                            'application_number' => $application?->application_number,

                            'applicant' => $application?->user?->name,

                            'programme' => $application?->programme?->name,

                            'old_status' => $history->old_status,

                            'status' => $history->new_status,

                            'officer' => $history->officer?->name,

                            'created_at' => $history->changed_at,
                        ];
                    }
                );
            })
            ->sortByDesc('created_at')
            ->take(12)
            ->values()
            ->all();
    }

    /**
     * Human-readable institutional admission outcome.
     */
    protected function statusLabel(string $status): string
    {
        return match ($status) {
            AdmissionDecision::ADMISSION_STATUS_PENDING => 'Pending',
            AdmissionDecision::ADMISSION_STATUS_ADMITTED => 'Admitted',
            AdmissionDecision::ADMISSION_STATUS_NOT_ADMITTED => 'Not Admitted',
            AdmissionDecision::ADMISSION_STATUS_WAITLISTED => 'Waitlisted',
            AdmissionDecision::ADMISSION_STATUS_DEFERRED => 'Deferred',

            default => str($status)
                ->replace('_', ' ')
                ->title()
                ->toString(),
        };
    }
}
