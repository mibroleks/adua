<?php

/*
Component: List Applications Page (Hardened & Enriched)
File Path: app/Filament/Resources/ApplicationResource/Pages/ListApplications.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament list view for applications.
Officers can browse applications with filters and summary columns.
Manual creation is disabled to enforce portal-only submission.

Status: Hardened & Enriched (namespace corrected, v5 compatible)
Version: 2.1 (registered ApplicationOverview header widget)
*/

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use App\Filament\Resources\ApplicationResource\Widgets\ApplicationOverview;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;

class ListApplications extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = ApplicationResource::class;

    /**
     * Register the Applications KPI widget above the table.
     *
     * ExposesTableToWidgets shares the current table state with
     * widgets using InteractsWithPageTable.
     */
    protected function getHeaderWidgets(): array
    {
        return [
            ApplicationOverview::class,
        ];
    }

    /**
     * The KPI widget should occupy the full page width.
     * Its own StatsOverviewWidget configuration controls
     * the arrangement of the individual statistics.
     */
    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    // Disable header actions (no create)
    protected function getHeaderActions(): array
    {
        return [];
    }

    // Summary columns
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('application_number')
                ->label('Application No.')
                ->searchable()
                ->sortable(),

            TextColumn::make('user.name')
                ->label('Applicant')
                ->searchable(),

            TextColumn::make('programme.name')
                ->label('Programme')
                ->sortable(),

            TextColumn::make('application_status')
                ->label('Application Status')
                ->badge()
                ->sortable()
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'DRAFT'               => 'Draft',
                    'SUBMITTED'           => 'Submitted',
                    'UNDER_REVIEW'        => 'Under Review',
                    'APPROVED'            => 'Approved',
                    'REJECTED'            => 'Rejected',
                    'CORRECTION_REQUIRED' => 'Correction Required',
                    default               => $state ?? '—',
                })
                ->color(fn (?string $state): string => match ($state) {
                    'DRAFT'               => 'gray',
                    'SUBMITTED'           => 'info',
                    'UNDER_REVIEW'        => 'warning',
                    'APPROVED'            => 'success',
                    'REJECTED'            => 'danger',
                    'CORRECTION_REQUIRED' => 'info',
                    default               => 'gray',
                }),

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

            TextColumn::make('formatted_application_fee')
                ->label('Fee')
                ->money('NGN', true)
                ->sortable(),

            TextColumn::make('submitted_at')
                ->label('Submitted')
                ->dateTime('d M Y, H:i')
                ->sortable()
                ->placeholder('Not submitted'),

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
        ];
    }

    // Filters
    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('application_status')
                ->label('Application Status')
                ->options([
                    'DRAFT'               => 'Draft',
                    'SUBMITTED'           => 'Submitted',
                    'UNDER_REVIEW'        => 'Under Review',
                    'APPROVED'            => 'Approved',
                    'REJECTED'            => 'Rejected',
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
                        ->when(
                            $data['from'],
                            fn ($q) => $q->whereDate('submitted_at', '>=', $data['from'])
                        )
                        ->when(
                            $data['until'],
                            fn ($q) => $q->whereDate('submitted_at', '<=', $data['until'])
                        )
                ),
        ];
    }
}
