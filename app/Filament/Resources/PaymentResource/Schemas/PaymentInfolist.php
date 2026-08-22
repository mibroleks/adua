<?php

/*
Component: Payment Infolist
File Path: app/Filament/Resources/PaymentResource/Schemas/PaymentInfolist.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the Filament infolist schema for viewing payment details.
Payments are immutable audit records created via PaymentService,
so officers can only view structured details (not edit).
Includes reference, type, amount, gateway, status, timestamps, metadata,
and reconciliation fields for finance integration.

Status: ✅ Production Ready
Version: 2.0 (added reconciliation fields)
*/

namespace App\Filament\Resources\PaymentResource\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use App\Models\Payment;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Payment Information')
                ->columns(3)
                ->schema([
                    TextEntry::make('reference')->label('Reference')->copyable(),
                    TextEntry::make('transaction_reference')->label('Transaction Reference')->placeholder('—'),
                    TextEntry::make('application.application_number')->label('Application No.'),
                    TextEntry::make('application.user.name')->label('Applicant'),
                    TextEntry::make('payment_type')->label('Payment Type'),
                    TextEntry::make('amountInNaira')->money('NGN', true)->label('Amount'),
                    TextEntry::make('currency')->label('Currency'),
                    TextEntry::make('gateway')->label('Gateway'),
                    TextEntry::make('status')
                        ->badge()
                        ->colors([
                            'success' => Payment::STATUS_SUCCESS,
                            'danger'  => Payment::STATUS_FAILED,
                            'warning' => Payment::STATUS_PENDING,
                        ])
                        ->label('Status'),
                    TextEntry::make('paid_at')->dateTime('d M Y, H:i')->label('Paid At')->placeholder('—'),
                    TextEntry::make('verified_at')->dateTime('d M Y, H:i')->label('Verified At')->placeholder('—'),
                ]),

            Section::make('Finance Reconciliation')
                ->columns(3)
                ->schema([
                    TextEntry::make('balance_after_in_naira')
                        ->label('Balance After')
                        ->money('NGN', true)
                        ->placeholder('—'),
                    TextEntry::make('ledger_code')->label('Ledger Code')->placeholder('—'),
                    TextEntry::make('narration')->label('Narration')->placeholder('—')->columnSpanFull(),
                ]),

            Section::make('Metadata')
                ->columns(1)
                ->schema([
                    KeyValueEntry::make('metadata')
                        ->label('Gateway Metadata')
                        ->columnSpanFull()
                        ->placeholder('—'),
                ]),
        ]);
    }
}
