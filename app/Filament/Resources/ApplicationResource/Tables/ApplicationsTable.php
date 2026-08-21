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

Status: ✅ Production Ready
Version: 3.2 (fee accessor + latest decision)
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

                // Application Number
                TextColumn::make('application_number')
                    ->label('Application No.')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Application number copied')
                    ->weight('bold'),

                // Applicant
                TextColumn::make('user.name')
                    ->label('Applicant')
                    ->searchable()
                    ->sortable(),

                // Programme
                TextColumn::make('programme.name')
                    ->label('Programme')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                // Application Status
                TextColumn::make('application_status')
                    ->label('Application Status')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'DRAFT'              => 'Draft',
                        'SUBMITTED'          => 'Submitted',
                        'UNDER_REVIEW'       => 'Under Review',
                        'APPROVED'           => 'Approved',
                        'REJECTED'           => 'Rejected',
                        'CORRECTION_REQUIRED'=> 'Correction Required',
                        default              => $state ?? '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'DRAFT'              => 'gray',
                        'SUBMITTED'          => 'info',
                        'UNDER_REVIEW'       => 'warning',
                        'APPROVED'           => 'success',
                        'REJECTED'           => 'danger',
                        'CORRECTION_REQUIRED'=> 'info',
                        default              => 'gray',
                    }),

                // Payment Status
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'PENDING' => 'Pending',
                        'SUCCESS' => 'Paid',
                        'FAILED'  => 'Failed',
                        default   => $state ?? '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'SUCCESS' => 'success',
                        'FAILED'  => 'danger',
                        default   => 'gray',
                    }),

                // ✅ Application Fee (use accessor)
                TextColumn::make('formatted_application_fee')
                    ->label('Fee')
                    ->money('NGN', true)
                    ->sortable(),

                // Submitted At
                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('Not submitted'),

                // ✅ Decision (latest)
                TextColumn::make('decision.decision')
                    ->label('Decision')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'APPROVED' => 'Approved',
                        'REJECTED' => 'Rejected',
                        default    => 'Pending',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'APPROVED' => 'success',
                        'REJECTED' => 'danger',
                        default    => 'gray',
                    })
                    ->placeholder('Pending'),
            ])

            ->filters([
                SelectFilter::make('application_status')
                    ->label('Application Status')
                    ->options([
                        'DRAFT'        => 'Draft',
                        'SUBMITTED'    => 'Submitted',
                        'UNDER_REVIEW' => 'Under Review',
                        'APPROVED'     => 'Approved',
                        'REJECTED'     => 'Rejected',
                        'CORRECTION_REQUIRED' => 'Correction Required',
                    ]),

                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'PENDING' => 'Pending',
                        'SUCCESS' => 'Paid',
                        'FAILED'  => 'Failed',
                    ]),

                Filter::make('submitted_at')
                    ->label('Submission Date')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn ($query, array $data) =>
                        $query
                            ->when($data['from'], fn ($q) => $q->whereDate('submitted_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('submitted_at', '<=', $data['until']))
                    ),
            ])

            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Application $record): string =>
                        ApplicationResource::getUrl('view', ['record' => $record])
                    ),
            ])

            ->toolbarActions([]);
    }
}
