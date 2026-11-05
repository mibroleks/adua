<?php

/**
 * Component: Officer Admissions Dashboard
 * File Path: app/Filament/Pages/AdmissionDashboard.php
 * Company: Ygrace Tech
 * Author: Ibrahim Olalekan
 *
 * Purpose:
 * Main dashboard for the Officer Admissions Panel.
 *
 * Responsibilities:
 * - Load admissions dashboard data.
 * - Load institutional admission outcome analytics.
 * - Manage dashboard workspace navigation.
 * - Manage application processing and admission outcome filters independently.
 * - Apply and reset dashboard filters.
 * - Pass combined dashboard data to the custom Blade interface.
 *
 * Important:
 * - Does not modify the Student Admission Portal.
 * - Does not modify the Applications Register.
 * - Does not modify the admission application lifecycle.
 * - Keeps application processing decisions separate from
 *   institutional admission outcomes.
 * - Uses read-only reporting services.
 * - Does not modify admission decisions or historical records.
 */

namespace App\Filament\Pages;

use App\Services\AdmissionDecisionDashboardService;
use App\Services\AdmissionsDashboardService;
use Filament\Pages\Page;

class AdmissionDashboard extends Page
{
    /**
     * Dashboard view.
     */
    protected string $view = 'filament.pages.admission-dashboard';

    /**
     * Use the Officer panel dashboard route.
     */
    protected static ?string $slug = 'dashboard';

    /**
     * Display the Admissions Dashboard in the Officer sidebar.
     */
    protected static bool $shouldRegisterNavigation = true;

    /**
     * Sidebar navigation label.
     */
    protected static ?string $navigationLabel = 'Admissions Dashboard';

    /**
     * Sidebar navigation icon.
     *
     * Match the parent Filament property type.
     */
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-pie';

    /**
     * Display the dashboard near the top of the sidebar.
     */
    protected static ?int $navigationSort = 1;

    /**
     * Dashboard page title.
     */
    protected static ?string $title = 'Admissions Dashboard';

    /**
     * Active dashboard workspace.
     *
     * Supported workspaces:
     * - applications: Application Management.
     * - outcomes: Institutional Admission Outcomes.
     *
     * The default workspace is Application Management.
     */
    public string $activeTab = 'applications';

    /**
     * Dashboard filter values.
     *
     * Application processing status and institutional
     * admission outcome status remain separate.
     */

    public string $period = 'all_time';

    /**
     * Application processing status.
     *
     * Examples:
     * - Submitted
     * - Under Review
     * - Approved
     * - Rejected
     */
    public string $status = 'all';

    /**
     * Institutional admission outcome status.
     *
     * Supported values:
     * - all
     * - PENDING
     * - ADMITTED
     * - NOT_ADMITTED
     * - WAITLISTED
     * - DEFERRED
     */
    public string $admissionStatus = 'all';

    public string $programmeId = '';

    public string $referralSource = 'all';

    public string $paymentStatus = 'all';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    /**
     * Combined dashboard information.
     *
     * Existing application dashboard keys are preserved.
     *
     * Additional dataset:
     * - admission_decisions
     *
     * Institutional admission outcome reporting is kept
     * separate from application processing analytics.
     */
    public array $dashboardData = [];

    /**
     * Available dashboard filter options.
     *
     * Application dashboard remains the source of the
     * shared filter dropdown options.
     */
    public array $filterOptions = [];

    /**
     * Load dashboard information when the page opens.
     */
    public function mount(
        AdmissionsDashboardService $dashboardService,
        AdmissionDecisionDashboardService $admissionDecisionDashboardService
    ): void {
        $this->refreshDashboard(
            $dashboardService,
            $admissionDecisionDashboardService
        );
    }

    /**
     * Switch between dashboard workspaces.
     *
     * This method only changes the active presentation workspace.
     * It does not modify application records, admission decisions,
     * filters, or reporting data.
     *
     * Supported values:
     * - applications
     * - outcomes
     */
    public function switchTab(string $tab): void
    {
        if (! in_array($tab, ['applications', 'outcomes'], true)) {
            return;
        }

        $this->activeTab = $tab;
    }

    /**
     * Apply the selected dashboard filters.
     *
     * Application analytics use the application processing
     * status filter.
     *
     * Institutional admission analytics use the separate
     * admission outcome status filter.
     */
    public function applyFilters(
        AdmissionsDashboardService $dashboardService,
        AdmissionDecisionDashboardService $admissionDecisionDashboardService
    ): void {
        $this->refreshDashboard(
            $dashboardService,
            $admissionDecisionDashboardService
        );
    }

    /**
     * Reset all dashboard filters to their defaults.
     *
     * The active workspace is deliberately preserved.
     */
    public function resetDashboardFilters(
        AdmissionsDashboardService $dashboardService,
        AdmissionDecisionDashboardService $admissionDecisionDashboardService
    ): void {
        $this->period = 'all_time';

        $this->status = 'all';

        $this->admissionStatus = 'all';

        $this->programmeId = '';

        $this->referralSource = 'all';

        $this->paymentStatus = 'all';

        $this->dateFrom = null;

        $this->dateTo = null;

        $this->refreshDashboard(
            $dashboardService,
            $admissionDecisionDashboardService
        );
    }

    /**
     * Retrieve the combined dashboard information.
     *
     * Application analytics remain the primary dashboard dataset.
     *
     * Institutional admission outcome analytics are added
     * under the separate admission_decisions key.
     *
     * No existing application dashboard keys are replaced.
     */
    protected function refreshDashboard(
        AdmissionsDashboardService $dashboardService,
        AdmissionDecisionDashboardService $admissionDecisionDashboardService
    ): void {
        /*
         * Shared reporting filters.
         *
         * Both reporting services retain the existing cohort
         * and reporting filters.
         *
         * Application processing status and institutional
         * admission outcome status remain separate parameters.
         */
        $filters = [
            'period' => $this->period,

            'status' => $this->status,

            'admission_status' => $this->admissionStatus,

            'programme_id' => $this->programmeId !== ''
                ? $this->programmeId
                : null,

            'referral_source' => $this->referralSource,

            'payment_status' => $this->paymentStatus,

            'date_from' => $this->dateFrom,

            'date_to' => $this->dateTo,
        ];

        /*
         * Load the existing application dashboard dataset.
         *
         * The AdmissionsDashboardService remains responsible
         * for application processing analytics.
         */
        $applicationDashboardData = $dashboardService->getDashboardData(
            $filters
        );

        /*
         * Load institutional admission outcome analytics.
         *
         * The AdmissionDecisionDashboardService remains responsible
         * for institutional admission reporting.
         *
         * The admission_status filter is independent of
         * the application processing status filter.
         */
        $admissionDecisionData = $admissionDecisionDashboardService
            ->getDashboardData($filters);

        /*
         * Preserve the original dashboard structure and
         * append the separate institutional admission dataset.
         */
        $this->dashboardData = array_merge(
            $applicationDashboardData,
            [
                'admission_decisions' => $admissionDecisionData,
            ]
        );

        /*
         * Preserve the existing application dashboard
         * as the source of shared filter options.
         */
        $this->filterOptions = $applicationDashboardData['filter_options'] ?? [];
    }
}
