<?php

/*
Component: List Payments Page
File Path: app/Filament/Resources/PaymentResource/Pages/ListPayments.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament page for listing payment records.
Payments are immutable audit records created via PaymentService,
so officers can only view them (no manual create).
Supports filters and read-heavy administration.

Status: ✅ Production Ready
Version: 1.5 (Filament v3 compatible)
*/

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    /**
     * Override header actions — no CreateAction for payments.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
