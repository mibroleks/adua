<?php

/*
Component: Applications Table
File Path: app/Filament/Resources/ApplicationResource/Tables/ApplicationsTable.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the Filament table schema for listing application records.

The table is intentionally read-oriented:
- Officers can search and filter applications.
- Officers can open the complete application record.
- Application lifecycle status is taken from application_status.
- Payment status is displayed independently from application status.
- Submitted date uses submitted_at.
- Record actions use the Filament v5 action API.

Status: Production Ready
Version: 3.0
Filament: 5.x
*/

namespace App\Filament\Resources\ApplicationResource\Tables;

use App\Filament\Resources\ApplicationResource;
use App\Models\Application;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\Action;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                /*
                |--------------------------------------------------------------------------
                | Application Number
                |--------------------------------------------------------------------------
                */
                TextColumn::make('application_number')
                    ->label('Application No.')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Application number copied')
                    ->weight('bold'),

                /*
                |--------------------------------------------------------------------------
                | Applicant
                |--------------------------------------------------------------------------
                */
                TextColumn::make('user.name')
                    ->label('Applicant')
                    ->searchable()
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | Programme
                |--------------------------------------------------------------------------
                */
                TextColumn::make('programme.name')
                    ->label('Programme')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                /*
                |--------------------------------------------------------------------------
                | Application Status
                |--------------------------------------------------------------------------
                |
                | IMPORTANT:
                | The Application model uses application_status,
                | not status.
                |
                */
                TextColumn::make('application_status')
                    ->label('Application Status')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            'DRAFT'        => 'Draft',
                            'SUBMITTED'    => 'Submitted',
                            'UNDER_REVIEW' => 'Under Review',
                            'APPROVED'     => 'Approved',
                            'REJECTED'     => 'Rejected',
                            default        => $state ?? '—',
                        }
                    )
                    ->color(
                        fn (?string $state): string => match ($state) {
                            'DRAFT'        => 'gray',
                            'SUBMITTED'    => 'info',
                            'UNDER_REVIEW' => 'warning',
                            'APPROVED'     => 'success',
                            'REJECTED'     => 'danger',
                            default        => 'gray',
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | Payment Status
                |--------------------------------------------------------------------------
                |
                | Payment status is deliberately independent from
                | application lifecycle status.
                |
                */
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            'PENDING' => 'Pending',
                            'SUCCESS' => 'Paid',
                            'FAILED'  => 'Failed',
                            default   => $state ?? '—',
                        }
                    )
                    ->color(
                        fn (?string $state): string => match ($state) {
                            'PENDING' => 'warning',
                            'SUCCESS' => 'success',
                            'FAILED'  => 'danger',
                            default   => 'gray',
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | Application Fee
                |--------------------------------------------------------------------------
                */
                TextColumn::make('application_fee')
                    ->label('Fee')
                    ->money('NGN')
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | Submitted At
                |--------------------------------------------------------------------------
                |
                | Do not use created_at here.
                | submitted_at represents the actual application submission.
                |
                */
                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('Not submitted'),

                /*
                |--------------------------------------------------------------------------
                | Decision
                |--------------------------------------------------------------------------
                */
                TextColumn::make('decision.status')
                    ->label('Decision')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            'APPROVED' => 'Approved',
                            'REJECTED' => 'Rejected',
                            default    => $state ?? 'Pending',
                        }
                    )
                    ->color(
                        fn (?string $state): string => match ($state) {
                            'APPROVED' => 'success',
                            'REJECTED' => 'danger',
                            default    => 'gray',
                        }
                    )
                    ->placeholder('Pending'),
            ])

            /*
            |--------------------------------------------------------------------------
            | Filters
            |--------------------------------------------------------------------------
            */
            ->filters([

                /*
                | Application lifecycle status
                */
                SelectFilter::make('application_status')
                    ->label('Application Status')
                    ->options([
                        'DRAFT'        => 'Draft',
                        'SUBMITTED'    => 'Submitted',
                        'UNDER_REVIEW' => 'Under Review',
                        'APPROVED'     => 'Approved',
                        'REJECTED'     => 'Rejected',
                    ]),

                /*
                | Payment status
                */
                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'PENDING' => 'Pending',
                        'SUCCESS' => 'Paid',
                        'FAILED'  => 'Failed',
                    ]),

                /*
                | Submission date range
                */
                Filter::make('submitted_at')
                    ->label('Submission Date')
                    ->form([
                        DatePicker::make('from')
                            ->label('From'),

                        DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->query(
                        fn ($query, array $data) => $query
                            ->when(
                                $data['from'] ?? null,
                                fn ($query, $date) =>
                                    $query->whereDate(
                                        'submitted_at',
                                        '>=',
                                        $date
                                    )
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn ($query, $date) =>
                                    $query->whereDate(
                                        'submitted_at',
                                        '<=',
                                        $date
                                    )
                            )
                    ),
            ])

            /*
            |--------------------------------------------------------------------------
            | Record Actions
            |--------------------------------------------------------------------------
            |
            | Filament 5:
            | use Filament\Actions\Action
            |
            | NOT:
            | Filament\Tables\Actions\Action
            |
            */
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(
                        fn (Application $record): string =>
                            ApplicationResource::getUrl(
                                'view',
                                ['record' => $record]
                            )
                    ),

                /*
                |--------------------------------------------------------------------------
                | Optional direct edit action
                |--------------------------------------------------------------------------
                |
                | Keep this disabled for now because your architecture says
                | applicant data should not be casually edited by officers.
                |
                */
            ])

            /*
            |--------------------------------------------------------------------------
            | Bulk Actions
            |--------------------------------------------------------------------------
            */
            ->toolbarActions([]);
    }
}