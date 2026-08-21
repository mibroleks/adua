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
Includes reference, type, amount, gateway, status, timestamps, and metadata.

Status: ✅ Production Ready
Version: 1.7 (ensured amount display uses accessor consistently)
*/

namespace App\Filament\Resources\PaymentResource\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\KeyValueEntry;
use App\Models\Payment;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('reference')
                ->label('Reference'),

            TextEntry::make('transaction_reference')
                ->label('Transaction Reference'),

            TextEntry::make('application.application_number')
                ->label('Application No.'),

            TextEntry::make('application.user.name')
                ->label('Applicant'),

            TextEntry::make('payment_type')
                ->label('Payment Type'),

            // ✅ Use accessor to display amount in naira
            TextEntry::make('amountInNaira')
                ->money('NGN', true)
                ->label('Amount'),

            TextEntry::make('currency')
                ->label('Currency'),

            TextEntry::make('gateway')
                ->label('Gateway'),

            TextEntry::make('status')
                ->badge()
                ->colors([
                    'success' => Payment::STATUS_SUCCESS,
                    'danger'  => Payment::STATUS_FAILED,
                    'warning' => Payment::STATUS_PENDING,
                ])
                ->label('Status'),

            TextEntry::make('paid_at')
                ->dateTime()
                ->label('Paid At'),

            TextEntry::make('verified_at')
                ->dateTime()
                ->label('Verified At'),

            KeyValueEntry::make('metadata')
                ->label('Gateway Metadata')
                ->columnSpanFull(),
        ]);
    }
}
