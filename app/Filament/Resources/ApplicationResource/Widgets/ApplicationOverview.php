<?php

/*
Component: Application Overview Widget
File Path: app/Filament/Resources/ApplicationResource/Widgets/ApplicationOverview.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Displays premium live application performance indicators
above the Applications register using a fully custom Filament widget.

KPIs:
- Total Applications
- Unique Applicants
- Partner Referrals
- Dynamic individual Referral Partner KPIs
- Direct / Organic Applications
- Submitted Applications
- Applications Under Review
- Approved Applications
- Rejected Applications

Referral Performance:
- Total applications per referral partner.
- Unique applicants per referral partner.
- Approved applications per referral partner.
- Approved applicants per referral partner.
- Applicant admission conversion rate.
- Historical visibility for inactive referral partners.
- Direct / Organic referral performance.

Architecture:
- Uses a dedicated Blade view for complete presentation control.
- Reads from the active Applications table query.
- Respects the table's active filters and resource query scope.
- Uses ReferralPerformanceService as the shared reporting source.
- Counts unique applicants using distinct user accounts.
- Dynamically discovers registered referral partners.
- Displays zero-count cards for registered partners.
- Preserves historical referral visibility for inactive partners.
- Does not modify application records.
- Does not participate in admission or payment decisions.
- Keeps application statistics separate from presentation.

Status: Implementation
Version: 3.0 (shared referral performance reporting)
Filament: 5.x
*/

namespace App\Filament\Resources\ApplicationResource\Widgets;

use App\Filament\Resources\ApplicationResource\Pages\ListApplications;
use App\Services\ReferralPerformanceService;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class ApplicationOverview extends Widget
{
    use InteractsWithPageTable;

    /**
     * Dedicated Blade view.
     *
     * The view provides complete control over the KPI layout
     * without depending on Filament's StatsOverviewWidget.
     */
    protected string $view = 'filament.widgets.application-overview';

    /**
     * Allow the custom widget to occupy the full header width.
     */
    protected int | string | array $columnSpan = 'full';

    /**
     * Connect this widget to the Applications list page.
     *
     * InteractsWithPageTable uses this page to retrieve
     * the actual Applications table query.
     */
    protected function getTablePage(): string
    {
        return ListApplications::class;
    }

    /**
     * Supply live application statistics to the custom Blade view.
     *
     * All application calculations use the active Applications
     * table query, including its active filters and resource scope.
     *
     * Referral reporting is delegated to ReferralPerformanceService
     * to ensure consistent figures across reporting screens.
     */
    protected function getViewData(): array
    {
        $query = $this->getPageTableQuery();

        /*
         * ---------------------------------------------------------
         * Shared referral performance
         * ---------------------------------------------------------
         *
         * The service receives the active Applications table query.
         *
         * Its calculations therefore respect the current table
         * filters and resource query scope.
         *
         * It also provides:
         * - Partner application totals.
         * - Unique applicants.
         * - Approved applications.
         * - Approved applicants.
         * - Applicant admission conversion.
         * - Direct / Organic performance.
         *
         * Inactive and zero-count partners remain visible.
         */

        $referralPerformance = app(ReferralPerformanceService::class)
            ->getPerformance($query);

        $referralSources = collect(
            $referralPerformance['sources'] ?? []
        );

        /*
         * ---------------------------------------------------------
         * Application totals
         * ---------------------------------------------------------
         */

        $total = (clone $query)->count();

        /*
         * ---------------------------------------------------------
         * Unique applicants
         * ---------------------------------------------------------
         *
         * Count distinct student accounts rather than application
         * records.
         *
         * A student with multiple applications is counted once.
         *
         * This overall KPI uses the active table query directly
         * to avoid summing unique applicants across referral
         * sources and potentially counting the same account twice.
         */

        $uniqueApplicants = (clone $query)
            ->whereNotNull('applications.user_id')
            ->distinct()
            ->count('applications.user_id');

        /*
         * ---------------------------------------------------------
         * Referral source totals
         * ---------------------------------------------------------
         */

        $partnerSources = $referralSources
            ->where('source_type', 'partner');

        $directSource = $referralSources
            ->firstWhere('source_type', 'direct');

        $partnerReferrals = (int) $partnerSources
            ->sum('total_applications');

        $directApplications = (int) (
            $directSource['total_applications'] ?? 0
        );

        /*
         * ---------------------------------------------------------
         * Dynamic referral partner KPI cards
         * ---------------------------------------------------------
         *
         * Each partner card uses the shared performance service.
         *
         * The card's primary value remains the total number of
         * applications, preserving the existing overview layout.
         *
         * The description now provides additional recruitment
         * quality and admission conversion information.
         */

        $partnerStats = $partnerSources
            ->map(function (array $source) use ($total): array {

                $applications = (int) (
                    $source['total_applications'] ?? 0
                );

                $applicants = (int) (
                    $source['unique_applicants'] ?? 0
                );

                $approvedApplications = (int) (
                    $source['approved_applications'] ?? 0
                );

                $approvedApplicants = (int) (
                    $source['approved_applicants'] ?? 0
                );

                $conversionRate = (float) (
                    $source['applicant_admission_rate'] ?? 0
                );

                $inactiveLabel = ! ($source['is_active'] ?? true)
                    ? ' • Inactive partner'
                    : '';

                return [

                    'key' => 'partner_' . (
                        $source['referral_partner_id'] ?? 'unknown'
                    ),

                    'label' => $source['name'],

                    'value' => number_format($applications),

                    'description' => sprintf(
                        '%s applicants • %s approved applications • %s approved applicants • %s%% conversion%s',
                        number_format($applicants),
                        number_format($approvedApplications),
                        number_format($approvedApplicants),
                        number_format($conversionRate, 1),
                        $inactiveLabel,
                    ),

                    'icon' => 'heroicon-o-user-group',

                    'color' => 'success',

                ];

            })
            ->values()
            ->all();

        /*
         * ---------------------------------------------------------
         * Application lifecycle statuses
         * ---------------------------------------------------------
         *
         * Preserve the existing status KPI behaviour.
         */

        $submitted = $this->countByStatus(
            $query,
            'SUBMITTED'
        );

        $underReview = $this->countByStatus(
            $query,
            'UNDER_REVIEW'
        );

        $approved = $this->countByStatus(
            $query,
            'APPROVED'
        );

        $rejected = $this->countByStatus(
            $query,
            'REJECTED'
        );

        /*
         * ---------------------------------------------------------
         * Referral percentages
         * ---------------------------------------------------------
         */

        $partnerPercentage = $this->percentage(
            $partnerReferrals,
            $total
        );

        $directPercentage = $this->percentage(
            $directApplications,
            $total
        );

        /*
         * ---------------------------------------------------------
         * Custom widget presentation data
         * ---------------------------------------------------------
         *
         * Keep the existing stats array contract so the current
         * Blade view does not require a structural change.
         *
         * Dynamic partner cards are inserted automatically
         * between the aggregate referral and direct application
         * cards.
         */

        return [

            'stats' => [

                [
                    'key' => 'total',
                    'label' => 'Total Applications',
                    'value' => number_format($total),
                    'description' => 'All applications in the current view',
                    'icon' => 'heroicon-o-document-text',
                    'color' => 'primary',
                ],

                [
                    'key' => 'unique_applicants',
                    'label' => 'Unique Applicants',
                    'value' => number_format($uniqueApplicants),
                    'description' => 'Distinct student accounts in the current view',
                    'icon' => 'heroicon-o-users',
                    'color' => 'info',
                ],

                [
                    'key' => 'partner_referrals',
                    'label' => 'Partner Referrals',
                    'value' => number_format($partnerReferrals),
                    'description' => $partnerPercentage . '% of applications',
                    'icon' => 'heroicon-o-user-group',
                    'color' => 'success',
                ],

                ...$partnerStats,

                [
                    'key' => 'direct_applications',
                    'label' => 'Direct / Organic',
                    'value' => number_format($directApplications),
                    'description' => $directPercentage . '% of applications',
                    'icon' => 'heroicon-o-globe-alt',
                    'color' => 'gray',
                ],

                [
                    'key' => 'submitted',
                    'label' => 'Submitted',
                    'value' => number_format($submitted),
                    'description' => 'Applications formally submitted',
                    'icon' => 'heroicon-o-paper-airplane',
                    'color' => 'info',
                ],

                [
                    'key' => 'under_review',
                    'label' => 'Under Review',
                    'value' => number_format($underReview),
                    'description' => 'Applications awaiting review completion',
                    'icon' => 'heroicon-o-clock',
                    'color' => 'warning',
                ],

                [
                    'key' => 'approved',
                    'label' => 'Approved',
                    'value' => number_format($approved),
                    'description' => 'Applications with approved status',
                    'icon' => 'heroicon-o-check-circle',
                    'color' => 'success',
                ],

                [
                    'key' => 'rejected',
                    'label' => 'Rejected',
                    'value' => number_format($rejected),
                    'description' => 'Applications with rejected status',
                    'icon' => 'heroicon-o-x-circle',
                    'color' => 'danger',
                ],

            ],

        ];
    }

    /**
     * Count applications matching a lifecycle status.
     *
     * Clone the original query to avoid modifying the active
     * Applications table query.
     */
    private function countByStatus(
        Builder $query,
        string $status
    ): int {
        return (clone $query)
            ->where('applications.application_status', $status)
            ->count();
    }

    /**
     * Calculate a safe percentage.
     *
     * Returns zero when there are no applications in the
     * current filtered result set.
     */
    private function percentage(
        int $value,
        int $total
    ): string {
        if ($total === 0) {
            return '0.0';
        }

        return number_format(
            ($value / $total) * 100,
            1
        );
    }
}
