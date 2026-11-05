<?php

/*
Component: List Admission Decisions Page
File Path: app/Filament/Resources/AdmissionDecisionResource/Pages/ListAdmissionDecisions.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the institutional admission decision register.

Responsibilities:
- Display application-processing decision records.
- Provide access to individual admission decision details.
- Allow officers to open the controlled admission outcome workflow.
- Display admission decision intelligence and referral performance.
- Prevent manual creation of decision records.
- Preserve historical admission decision records.

Architecture:
- Admission decision records originate from AdmissionService.
- Application approval and actual admission remain separate.
- Admission outcomes are recorded through the View page.
- Admission overview reads from the current register query.
- Generic creation is disabled.
- Historical records must be preserved.

Version: 2.3
*/

namespace App\Filament\Resources\AdmissionDecisionResource\Pages;

use App\Filament\Resources\AdmissionDecisionResource;
use App\Filament\Resources\AdmissionDecisionResource\Widgets\AdmissionDecisionOverview;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListAdmissionDecisions extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = AdmissionDecisionResource::class;

    /*
    |--------------------------------------------------------------------------
    | Header widgets
    |--------------------------------------------------------------------------
    |
    | Displays admission intelligence above the admission decision register.
    |
    | The widget uses the current page table query so its statistics
    | and referral attribution follow the register's active query scope.
    |
    */

    protected function getHeaderWidgets(): array
    {
        return [
            AdmissionDecisionOverview::class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Header widget layout
    |--------------------------------------------------------------------------
    |
    | Gives the admission overview the full available page width.
    |
    */

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    /*
    |--------------------------------------------------------------------------
    | Header actions
    |--------------------------------------------------------------------------
    |
    | No Create action is provided.
    |
    | Decision records must be generated through the application
    | processing workflow managed by AdmissionService.
    |
    */

    protected function getHeaderActions(): array
    {
        return [];
    }
}
