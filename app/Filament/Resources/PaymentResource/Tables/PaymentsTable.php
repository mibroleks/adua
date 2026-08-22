<?php

/*
Component: Payments Table
File Path: app/Filament/Resources/PaymentResource/Tables/PaymentsTable.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the Filament table schema for listing payment records.
Payments are immutable audit records created via PaymentService,
so officers can only view them (no manual create/edit/delete).
Supports filters by status, type, gateway, and date.
Future-proofed with reconciliation fields for finance integration.

Status: ✅ Production Ready
Version: 2.0 (added reconciliation fields)
*/

namespace App\Filament\Resources\PaymentResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use App\Models\Payment;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Reference')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('application.application_number')
                    ->label('Application No.')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('application.user.name')
                    ->label('Applicant')
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Type')
                    ->sortable(),

                // ✅ Use accessor to display amount in naira
                Tables\Columns\TextColumn::make('amountInNaira')
                    ->money('NGN', true)
                    ->label('Amount')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gateway')
                    ->label('Gateway')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => Payment::STATUS_SUCCESS,
                        'danger'  => Payment::STATUS_FAILED,
                        'warning' => Payment::STATUS_PENDING,
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->label('Paid At'),

                Tables\Columns\TextColumn::make('verified_at')
                    ->dateTime()
                    ->label('Verified At'),

                /*
                |--------------------------------------------------------------------------
                | Finance Reconciliation Fields
                |--------------------------------------------------------------------------
                */
                Tables\Columns\TextColumn::make('balance_after_in_naira')
                    ->label('Balance After')
                    ->money('NGN', true)
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('ledger_code')
                    ->label('Ledger Code')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('narration')
                    ->label('Narration')
                    ->wrap()
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Payment::STATUS_PENDING => 'Pending',
                        Payment::STATUS_SUCCESS => 'Success',
                        Payment::STATUS_FAILED  => 'Failed',
                    ]),

                Tables\Filters\SelectFilter::make('payment_type')
                    ->options([
                        Payment::TYPE_APPLICATION_FEE => 'Application Fee',
                        Payment::TYPE_ACCEPTANCE_FEE  => 'Acceptance Fee',
                        Payment::TYPE_SCHOOL_FEE      => 'School Fee',
                        Payment::TYPE_SEMESTER_FEE    => 'Semester Fee',
                        Payment::TYPE_ACCOMMODATION   => 'Accommodation',
                        Payment::TYPE_OTHER           => 'Other',
                    ]),

                Tables\Filters\SelectFilter::make('gateway')
                    ->options([
                        'paystack'    => 'Paystack',
                        'flutterwave' => 'Flutterwave',
                    ]),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->recordActions([]) // No record actions; view handled by ViewPayment page
            ->bulkActions([]);  // No bulk delete/edit for immutable audit records
    }
}
