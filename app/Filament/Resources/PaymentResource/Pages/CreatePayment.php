<?php

/*
Component: Create Payment Page (Unused)
File Path: app/Filament/Resources/PaymentResource/Pages/CreatePayment.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Payments are immutable audit records created via PaymentService.
This page is intentionally disabled — officers cannot create payments manually.

Status: 🚫 Not in use
Version: 1.0
*/

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    /**
     * Override to disable creation.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        abort(403, 'Payments cannot be created manually.');
    }
}
