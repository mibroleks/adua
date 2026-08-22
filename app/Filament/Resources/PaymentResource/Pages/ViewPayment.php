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
Future-proofed with reconciliation fields for finance integration.

Status: ✅ Production Ready
Version: 2.0 (added reconciliation fields)
*/

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

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

    /**
     * Define structured view for payment details.
     */
    protected function getInfolist(): array
    {
        return [
            Section::make('Payment Information')
                ->columns(3)
                ->schema([
                    TextEntry::make('reference')->label('Reference')->copyable(),
                    TextEntry::make('transaction_reference')->label('Transaction Ref')->placeholder('—'),
                    TextEntry::make('payment_type')->label('Type'),
                    TextEntry::make('amountInNaira')->label('Amount')->money('NGN', true),
                    TextEntry::make('currency')->label('Currency'),
                    TextEntry::make('status')->label('Status')->badge()->color(fn (?string $state) => match ($state) {
                        'SUCCESS' => 'success',
                        'FAILED'  => 'danger',
                        'PENDING' => 'warning',
                        default   => 'gray',
                    }),
                    TextEntry::make('gateway')->label('Gateway'),
                    TextEntry::make('paid_at')->label('Paid At')->dateTime('d M Y, H:i')->placeholder('—'),
                    TextEntry::make('verified_at')->label('Verified At')->dateTime('d M Y, H:i')->placeholder('—'),
                ]),

            Section::make('Finance Reconciliation')
                ->columns(3)
                ->schema([
                    TextEntry::make('balance_after_in_naira')->label('Balance After')->money('NGN', true)->placeholder('—'),
                    TextEntry::make('ledger_code')->label('Ledger Code')->placeholder('—'),
                    TextEntry::make('narration')->label('Narration')->placeholder('—')->columnSpanFull(),
                ]),

            Section::make('Metadata')
                ->columns(1)
                ->schema([
                    TextEntry::make('metadata')->label('Gateway Metadata')->json()->placeholder('—'),
                ]),
        ];
    }
}
