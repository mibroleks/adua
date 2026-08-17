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

Status: ✅ Hardened & Enriched (namespace corrected, v5 compatible)
Version: 1.9 (Filament v5.7.6 compatible)
*/

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    // Disable header actions (no create)
    protected function getHeaderActions(): array
    {
        return [];
    }

    // Quick summary columns
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

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->sortable(),

            TextColumn::make('created_at')
                ->label('Submitted At')
                ->dateTime()
                ->sortable(),
        ];
    }

    // Filters for narrowing down applications
    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('status')
                ->label('Status')
                ->options([
                    'pending'   => 'Pending',
                    'reviewed'  => 'Reviewed',
                    'accepted'  => 'Accepted',
                    'rejected'  => 'Rejected',
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
        ];
    }
}
