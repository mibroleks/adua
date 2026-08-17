<?php

/*
Component: View Payment Page
File Path: app/Filament/Resources/PaymentResource/Pages/ViewPayment.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament page for viewing a single payment record.
Payments are immutable audit records created via PaymentService,
so officers can only view structured details (no manual edit).

Status: ✅ Production Ready
Version: 1.5 (Filament v3 compatible)
*/

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    /**
     * Override header actions — no EditAction for payments.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
