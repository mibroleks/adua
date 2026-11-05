<?php

/*
Component: Edit Admission Decision Page
File Path: app/Filament/Resources/AdmissionDecisionResource/Pages/EditAdmissionDecision.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Prevents unrestricted editing of admission decision records.

Architecture:
- Application-processing decisions are managed through AdmissionService.
- Institutional admission outcomes are managed through the
  dedicated View Admission Decision workflow.
- Historical decision records must remain auditable.
- Generic editing and deletion are disabled.

Version: 2.1
*/

namespace App\Filament\Resources\AdmissionDecisionResource\Pages;

use App\Filament\Resources\AdmissionDecisionResource;
use Filament\Resources\Pages\EditRecord;

class EditAdmissionDecision extends EditRecord
{
    protected static string $resource = AdmissionDecisionResource::class;

    /**
     * Admission decision records cannot be edited through generic CRUD.
     */
    public static function canAccess(array $parameters = []): bool
    {
        return false;
    }
}
