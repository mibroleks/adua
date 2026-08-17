<?php

/*
Component: View Application Page (Hardened)
File Path: app/Filament/Resources/ApplicationResource/Pages/ViewApplication.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament view page for a single application record.
Officers can inspect structured details via ApplicationInfolist.
Manual editing is disabled to enforce portal-only creation/update.
Adds header actions for dossier exports (Print, PDF, Excel, CSV).

Status: ✅ Hardened (namespace corrected, v5 compatible)
Version: 2.0 (Filament v5.7.6 compatible, with export actions)
*/

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Actions\ActionGroup;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    /**
     * Header actions: dossier exports only (no edit/delete).
     */
    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Actions\Action::make('print')
                    ->label('Print')
                    ->url(fn () => route('application.print', $this->record))
                    ->openUrlInNewTab(),

                Actions\Action::make('pdf')
                    ->label('PDF')
                    ->url(fn () => route('application.pdf', $this->record))
                    ->openUrlInNewTab(),

                Actions\Action::make('excel')
                    ->label('Excel')
                    ->url(fn () => route('application.export.excel', $this->record)),

                Actions\Action::make('csv')
                    ->label('CSV')
                    ->url(fn () => route('application.export.csv', $this->record)),
            ])
            ->label('Dossier Exports')
            ->icon('heroicon-o-arrow-down-tray'),
        ];
    }
}
