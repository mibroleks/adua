<?php

/*
Component: Admissions Dashboard Service
File Path: app/Services/AdmissionsDashboardService.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Central data provider for the Officer Admissions Dashboard.

Responsibilities:
- Admissions performance metrics
- Application status distribution
- Application trends
- Programme demand
- Referral partner performance
- Recent admissions activity
- Admissions action centre
- Dashboard-wide filtering

Architecture:
- Uses existing Application and related models.
- Reuses ReferralPerformanceService for consistent referral reporting.
- Does not modify the admissions lifecycle.
- Does not create or modify application records.
- Does not interfere with the Applications Register.
- Supports dynamic referral partners.
- Supports combined dashboard filters.

Status: Dashboard Implementation
Version: 1.1
*/

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\Programme;
use App\Models\ReferralPartner;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AdmissionsDashboardService
{
    /**
     * Supported application statuses.
     */
    protected array $statuses = [
        Application::STATUS_DRAFT,
        Application::STATUS_SUBMITTED,
        Application::STATUS_UNDER_REVIEW,
        Application::STATUS_CORRECTION_REQUIRED,
        Application::STATUS_APPROVED,
        Application::STATUS_REJECTED,
    ];

    /**
     * Supported payment statuses.
     */
    protected array $paymentStatuses = [
        Application::PAYMENT_PENDING,
        Application::PAYMENT_SUCCESS,
        Application::PAYMENT_FAILED,
    ];

    /**
     * Return the complete dashboard dataset.
     */
    public function getDashboardData(array $filters = []): array
    {
        $filters = $this->normaliseFilters($filters);

        $query = $this->filteredApplications($filters);

        return [
            'filters' => $filters,

            'summary' => $this->getSummary(
                clone $query
            ),

            'trends' => $this->getApplicationTrends(
                $filters
            ),

            'status_distribution' => $this->getStatusDistribution(
                clone $query
            ),

            'programme_demand' => $this->getProgrammeDemand(
                clone $query
            ),

            'referral_performance' => $this->getReferralPerformance(
                clone $query
            ),

            'recent_activity' => $this->getRecentActivity(
                $filters
            ),

            'action_centre' => $this->getActionCentre(
                $filters
            ),

            'filter_options' => $this->getFilterOptions(),
        ];
    }

    /**
     * Normalise incoming dashboard filters.
     */
    public function normaliseFilters(array $filters): array
    {
        $period = $filters['period'] ?? 'all_time';

        $allowedPeriods = [
            'all_time',
            'today',
            'this_week',
            'this_month',
            'this_year',
            'custom',
        ];

        if (! in_array($period, $allowedPeriods, true)) {
            $period = 'all_time';
        }

        $status = $filters['status'] ?? 'all';

        if (
            $status !== 'all' &&
            ! in_array($status, $this->statuses, true)
        ) {
            $status = 'all';
        }

        $paymentStatus = $filters['payment_status'] ?? 'all';

        if (
            $paymentStatus !== 'all' &&
            ! in_array($paymentStatus, $this->paymentStatuses, true)
        ) {
            $paymentStatus = 'all';
        }

        $programmeId = $filters['programme_id'] ?? 'all';

        if (
            $programmeId !== 'all' &&
            (! is_numeric($programmeId) || (int) $programmeId < 1)
        ) {
            $programmeId = 'all';
        }

        $referralSource = $filters['referral_source'] ?? 'all';

        if (
            $referralSource !== 'all' &&
            $referralSource !== 'direct' &&
            (! is_numeric($referralSource) || (int) $referralSource < 1)
        ) {
            $referralSource = 'all';
        }

        return [
            'period' => $period,
            'status' => $status,
            'payment_status' => $paymentStatus,

            'programme_id' => $programmeId === 'all'
                ? 'all'
                : (int) $programmeId,

            'referral_source' => is_numeric($referralSource)
                ? (int) $referralSource
                : $referralSource,

            'date_from' => $this->normaliseDate(
                $filters['date_from'] ?? null
            ),

            'date_to' => $this->normaliseDate(
                $filters['date_to'] ?? null
            ),
        ];
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
     * Build the shared application query.
     *
     * All dashboard sections use the same filter rules.
     */
    protected function filteredApplications(array $filters): Builder
    {
        $query = Application::query();

        $this->applyDateFilter($query, $filters);

        if ($filters['status'] !== 'all') {
            $query->where(
                'application_status',
                $filters['status']
            );
        }

        if ($filters['payment_status'] !== 'all') {
            $query->where(
                'payment_status',
                $filters['payment_status']
            );
        }

        if ($filters['programme_id'] !== 'all') {
            $query->where(
                'programme_id',
                $filters['programme_id']
            );
        }

        if ($filters['referral_source'] === 'direct') {
            $query->whereNull('referral_partner_id');
        } elseif (is_int($filters['referral_source'])) {
            $query->where(
                'referral_partner_id',
                $filters['referral_source']
            );
        }

        return $query;
    }

    /**
     * Apply application creation-date filtering.
     */
    protected function applyDateFilter(
        Builder $query,
        array $filters
    ): void {
        switch ($filters['period']) {
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
                if ($filters['date_from']) {
                    $query->where(
                        'created_at',
                        '>=',
                        Carbon::parse($filters['date_from'])->startOfDay()
                    );
                }

                if ($filters['date_to']) {
                    $query->where(
                        'created_at',
                        '<=',
                        Carbon::parse($filters['date_to'])->endOfDay()
                    );
                }
                break;
        }
    }

    /**
     * Admissions performance summary.
     */
    protected function getSummary(Builder $query): array
    {
        $total = (clone $query)->count();

        $statusCounts = (clone $query)
            ->selectRaw('application_status, COUNT(*) as total')
            ->groupBy('application_status')
            ->pluck('total', 'application_status');

        $paymentCounts = (clone $query)
            ->selectRaw('payment_status, COUNT(*) as total')
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        /*
         * Count distinct applicant accounts rather than application rows.
         * Repeat applications must not inflate applicant totals.
         */
        $uniqueApplicants = (clone $query)
            ->whereNotNull('user_id')
            ->distinct()
            ->count('user_id');

        $approvedApplicants = (clone $query)
            ->where(
                'application_status',
                Application::STATUS_APPROVED
            )
            ->whereNotNull('user_id')
            ->distinct()
            ->count('user_id');

        return [
            'total' => $total,

            'unique_applicants' => $uniqueApplicants,

            'approved_applicants' => $approvedApplicants,

            'draft' => (int) (
                $statusCounts[Application::STATUS_DRAFT] ?? 0
            ),

            'submitted' => (int) (
                $statusCounts[Application::STATUS_SUBMITTED] ?? 0
            ),

            'under_review' => (int) (
                $statusCounts[Application::STATUS_UNDER_REVIEW] ?? 0
            ),

            'correction_required' => (int) (
                $statusCounts[Application::STATUS_CORRECTION_REQUIRED] ?? 0
            ),

            'approved' => (int) (
                $statusCounts[Application::STATUS_APPROVED] ?? 0
            ),

            'rejected' => (int) (
                $statusCounts[Application::STATUS_REJECTED] ?? 0
            ),

            'payment_pending' => (int) (
                $paymentCounts[Application::PAYMENT_PENDING] ?? 0
            ),

            'payment_success' => (int) (
                $paymentCounts[Application::PAYMENT_SUCCESS] ?? 0
            ),

            'payment_failed' => (int) (
                $paymentCounts[Application::PAYMENT_FAILED] ?? 0
            ),

            'awaiting_review' => (int) (
                $statusCounts[Application::STATUS_SUBMITTED] ?? 0
            ),

            'awaiting_decision' => (int) (
                $statusCounts[Application::STATUS_UNDER_REVIEW] ?? 0
            ),
        ];
    }

    /**
     * Application status distribution.
     */
    protected function getStatusDistribution(Builder $query): array
    {
        $counts = (clone $query)
            ->selectRaw('application_status, COUNT(*) as total')
            ->groupBy('application_status')
            ->pluck('total', 'application_status');

        $distribution = [];

        foreach ($this->statuses as $status) {
            $distribution[] = [
                'status' => $status,
                'label' => $this->statusLabel($status),
                'total' => (int) ($counts[$status] ?? 0),
            ];
        }

        return $distribution;
    }

    /**
     * Application trends grouped by creation date.
     *
     * Uses application records rather than status-history events.
     */
    protected function getApplicationTrends(array $filters): array
    {
        $query = $this->filteredApplications($filters);

        $applications = $query
            ->select(['id', 'created_at'])
            ->orderBy('created_at')
            ->get();

        $grouping = match ($filters['period']) {
            'today', 'this_week', 'this_month' => 'day',
            default => 'month',
        };

        $grouped = $applications->groupBy(
            function ($application) use ($grouping) {
                return $grouping === 'day'
                    ? $application->created_at->format('Y-m-d')
                    : $application->created_at->format('Y-m');
            }
        );

        $labels = [];
        $values = [];

        foreach ($grouped as $key => $items) {
            $labels[] = $grouping === 'day'
                ? Carbon::parse($key)->format('d M')
                : Carbon::parse($key . '-01')->format('M Y');

            $values[] = $items->count();
        }

        return [
            'grouping' => $grouping,
            'labels' => $labels,
            'values' => $values,
            'total' => $applications->count(),
        ];
    }

    /**
     * Programme demand.
     */
    protected function getProgrammeDemand(Builder $query): array
    {
        return (clone $query)
            ->selectRaw('programme_id, COUNT(*) as total')
            ->with('programme:id,name')
            ->groupBy('programme_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($application) => [
                'programme_id' => $application->programme_id,
                'programme' => $application->programme?->name
                    ?? 'Unknown programme',
                'total' => (int) $application->total,
            ])
            ->values()
            ->all();
    }

    /**
     * Referral partner performance.
     *
     * Uses ReferralPerformanceService as the single source
     * of referral reporting calculations.
     *
     * Preserves the existing dashboard keys:
     * - id
     * - name
     * - total
     * - source
     *
     * Also supplies the additional performance metrics:
     * - unique_applicants
     * - paid_applications
     * - approved_applications
     * - approved_applicants
     * - rejected_applications
     * - multiple_application_applicants
     * - application_approval_rate
     * - applicant_admission_rate
     */
    protected function getReferralPerformance(Builder $query): array
    {
        $performance = app(ReferralPerformanceService::class)
            ->getPerformance($query);

        return collect($performance['sources'] ?? [])
            ->map(fn (array $source) => array_merge(
                [
                    'id' => $source['referral_partner_id'] ?? null,
                    'name' => $source['name'] ?? 'Unknown source',
                    'total' => (int) (
                        $source['total_applications'] ?? 0
                    ),
                    'source' => $source['source_type'] ?? 'direct',
                ],
                $source
            ))
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * Recent application and status-change activity.
     */
    protected function getRecentActivity(array $filters): array
    {
        $applications = $this->filteredApplications($filters)
            ->with(['user', 'programme'])
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($application) => [
                'type' => 'application_created',
                'title' => 'New application created',
                'description' => trim(
                    ($application->user?->name ?? 'Applicant') .
                    ' created ' .
                    $application->application_number
                ),
                'application_number' => $application->application_number,
                'status' => $application->application_status,
                'programme' => $application->programme?->name,
                'created_at' => $application->created_at,
            ]);

        $historyQuery = ApplicationStatusHistory::query()
            ->with(['application.user', 'application.programme']);

        $applicationIds = $this->filteredApplications($filters)
            ->select('id');

        $historyQuery->whereIn('application_id', $applicationIds);

        $history = $historyQuery
            ->latest('changed_at')
            ->limit(10)
            ->get()
            ->map(fn ($event) => [
                'type' => 'status_changed',
                'title' => 'Application status updated',
                'description' => trim(
                    ($event->application?->application_number ?? 'Application') .
                    ' changed to ' .
                    $this->statusLabel($event->new_status)
                ),
                'application_number' => $event->application?->application_number,
                'status' => $event->new_status,
                'programme' => $event->application?->programme?->name,
                'created_at' => $event->changed_at,
            ]);

        return $applications
            ->concat($history)
            ->sortByDesc('created_at')
            ->take(12)
            ->values()
            ->all();
    }

    /**
     * Admissions action centre.
     *
     * Each list uses the same dashboard filters.
     */
    protected function getActionCentre(array $filters): array
    {
        $base = $this->filteredApplications($filters);

        $awaitingReview = (clone $base)
            ->where(
                'application_status',
                Application::STATUS_SUBMITTED
            )
            ->with(['user', 'programme'])
            ->latest('submitted_at')
            ->limit(8)
            ->get();

        $awaitingDecision = (clone $base)
            ->where(
                'application_status',
                Application::STATUS_UNDER_REVIEW
            )
            ->with(['user', 'programme'])
            ->latest('updated_at')
            ->limit(8)
            ->get();

        $corrections = (clone $base)
            ->where(
                'application_status',
                Application::STATUS_CORRECTION_REQUIRED
            )
            ->with(['user', 'programme'])
            ->latest('updated_at')
            ->limit(8)
            ->get();

        return [
            'awaiting_review' => $awaitingReview,
            'awaiting_decision' => $awaitingDecision,
            'correction_required' => $corrections,
        ];
    }

    /**
     * Dashboard filter dropdown options.
     */
    protected function getFilterOptions(): array
    {
        return [
            'periods' => [
                'all_time' => 'All time',
                'today' => 'Today',
                'this_week' => 'This week',
                'this_month' => 'This month',
                'this_year' => 'This year',
                'custom' => 'Custom range',
            ],

            'statuses' => collect($this->statuses)
                ->mapWithKeys(fn ($status) => [
                    $status => $this->statusLabel($status),
                ])
                ->all(),

            'payment_statuses' => [
                Application::PAYMENT_PENDING => 'Pending',
                Application::PAYMENT_SUCCESS => 'Successful',
                Application::PAYMENT_FAILED => 'Failed',
            ],

            'programmes' => Programme::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all(),

            'referral_sources' => ReferralPartner::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->prepend('Direct / Organic', 'direct')
                ->all(),
        ];
    }

    /**
     * Human-readable application status.
     */
    protected function statusLabel(string $status): string
    {
        return match ($status) {
            Application::STATUS_DRAFT => 'Draft',
            Application::STATUS_SUBMITTED => 'Submitted',
            Application::STATUS_UNDER_REVIEW => 'Under Review',
            Application::STATUS_CORRECTION_REQUIRED => 'Correction Required',
            Application::STATUS_APPROVED => 'Approved',
            Application::STATUS_REJECTED => 'Rejected',
            default => str($status)
                ->replace('_', ' ')
                ->title()
                ->toString(),
        };
    }
}
