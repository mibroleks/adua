<?php

/*
Component: Create Admission Decision Page
File Path: app/Filament/Resources/AdmissionDecisionResource/Pages/CreateAdmissionDecision.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Prevents manual creation of admission decision records.

Architecture:
- Admission decision records originate from the application-processing
  workflow managed by AdmissionService.
- Institutional admission outcomes are recorded through the
  dedicated View Admission Decision workflow.
- Generic record creation is disabled.

Version: 2.1
*/

namespace App\Filament\Resources\AdmissionDecisionResource\Pages;

use App\Filament\Resources\AdmissionDecisionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmissionDecision extends CreateRecord
{
    protected static string $resource = AdmissionDecisionResource::class;

    /**
     * Admission decision records cannot be created manually.
     */
    public static function canAccess(array $parameters = []): bool
    {
        return false;
    }
}
