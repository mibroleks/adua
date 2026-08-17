<?php

/*
Component: Edit Payment Page (Unused)
File Path: app/Filament/Resources/PaymentResource/Pages/EditPayment.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Payments are immutable audit records created via PaymentService.
This page is intentionally disabled — officers cannot edit or delete payments manually.

Status: 🚫 Not in use
Version: 1.0
*/

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    /**
     * Override to disable editing.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        abort(403, 'Payments cannot be edited manually.');
    }

    /**
     * Override header actions — no delete/view for immutable records.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
