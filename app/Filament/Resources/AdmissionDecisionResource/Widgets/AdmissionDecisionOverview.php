<?php

/*
Component: Admission Decision Overview Widget
File Path: app/Filament/Resources/AdmissionDecisionResource/Widgets/AdmissionDecisionOverview.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides admission decision intelligence and referral performance
KPIs for the institutional Admission Decisions register.

Responsibilities:
- Read the active Admission Decisions table query.
- Respect the register's active filters.
- Display overall decision and admission outcome metrics.
- Display actual admitted-applicant conversion.
- Provide referral partner performance breakdowns.
- Include inactive and zero-count referral partners.
- Include Direct / Organic attribution separately.

Architecture:
- Reporting is read-only.
- Referral attribution is obtained from the original Application.
- Actual admission is determined by admission_status.
- Application approval is not treated as institutional admission.
- The existing Application Overview remains independent.
- No admission or application records are modified.

Status: Implementation
Version: 1.0 (Filament 5)
*/

namespace App\Filament\Resources\AdmissionDecisionResource\Widgets;

use App\Filament\Resources\AdmissionDecisionResource\Pages\ListAdmissionDecisions;
use App\Services\AdmissionDecisionReferralPerformanceService;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\Widget;

class AdmissionDecisionOverview extends Widget
{
    use InteractsWithPageTable;

    /*
    |--------------------------------------------------------------------------
    | Widget configuration
    |--------------------------------------------------------------------------
    */

    protected string $view =
        'filament.widgets.admission-decision-overview';

    protected int|string|array $columnSpan = 'full';

    /*
    |--------------------------------------------------------------------------
    | Register integration
    |--------------------------------------------------------------------------
    |
    | Connect this widget to the Admission Decisions register.
    |
    | InteractsWithPageTable supplies the current table query,
    | including the active register filters.
    |
    */

    public function getTablePage(): string
    {
        return ListAdmissionDecisions::class;
    }

    /*
    |--------------------------------------------------------------------------
    | Overview data
    |--------------------------------------------------------------------------
    |
    | The reporting service receives the filtered Admission Decisions
    | query. The widget does not create an independent query that
    | bypasses the register's active filters.
    |
    */

    protected function getViewData(): array
    {
        $query = $this->getPageTableQuery();

        $performance = app(
            AdmissionDecisionReferralPerformanceService::class
        )->getPerformance($query);

        $totals = $performance['totals'];

        /*
        |--------------------------------------------------------------------------
        | KPI definitions
        |--------------------------------------------------------------------------
        */

        $stats = [

            [
                'label' => 'Admission Decision Records',
                'value' => number_format($totals['total_decisions']),
                'description' => 'Total decisions in the filtered register',
                'icon' => 'heroicon-o-clipboard-document-check',
                'color' => 'primary',
                'type' => 'total',
            ],

            [
                'label' => 'Unique Applicants',
                'value' => number_format($totals['unique_applicants']),
                'description' => 'Distinct applicants represented in the register',
                'icon' => 'heroicon-o-users',
                'color' => 'info',
                'type' => 'applicants',
            ],

            [
                'label' => 'Actual Admitted Applicants',
                'value' => number_format($totals['admitted_applicants']),
                'description' => 'Unique applicants with an ADMITTED outcome',
                'icon' => 'heroicon-o-academic-cap',
                'color' => 'success',
                'type' => 'admitted',
            ],

            [
                'label' => 'Admission Conversion',
                'value' => number_format(
                    $totals['admission_conversion_rate'],
                    2
                ) . '%',
                'description' => 'Admitted applicants divided by unique applicants',
                'icon' => 'heroicon-o-chart-bar',
                'color' => 'success',
                'type' => 'conversion',
            ],

            [
                'label' => 'Waitlisted',
                'value' => number_format($totals['waitlisted_decisions']),
                'description' => 'Institutional waitlist outcomes',
                'icon' => 'heroicon-o-clock',
                'color' => 'warning',
                'type' => 'waitlisted',
            ],

            [
                'label' => 'Not Admitted',
                'value' => number_format($totals['not_admitted_decisions']),
                'description' => 'Recorded unsuccessful admission outcomes',
                'icon' => 'heroicon-o-x-circle',
                'color' => 'danger',
                'type' => 'not-admitted',
            ],

        ];

        return [
            'stats' => $stats,
            'sources' => $performance['sources'],
            'totals' => $totals,
        ];
    }
}
