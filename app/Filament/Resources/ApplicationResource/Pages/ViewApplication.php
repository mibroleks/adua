<?php

/*
Component: View Application Page (Hardened + Workflow)
File Path: app/Filament/Resources/ApplicationResource/Pages/ViewApplication.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament view page for a single application record.
Officers can inspect structured details via ApplicationInfolist.
Manual editing is disabled to enforce portal-only creation/update.
Adds header actions for dossier exports (Print, PDF, Excel, CSV)
and workflow actions (Approve, Reject, Request Correction, Start Review).

Status: 🚦 Integration / Hardening
Version: 2.3 (wired to corrected AdmissionService methods)
*/

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\User;
use App\Services\AdmissionService;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use Filament\Forms;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    /**
     * Header actions: dossier exports + workflow actions.
     */
    protected function getHeaderActions(): array
    {
        return [
            // Export actions
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

            // Workflow actions
            ActionGroup::make([
                Actions\Action::make('startReview')
                    ->label('Start Review')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Application $record) => $record->application_status === Application::STATUS_SUBMITTED)
                    ->action(fn (Application $record) =>
                        app(AdmissionService::class)->startReview($record, auth()->id())
                    ),

                Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Application $record) => $record->application_status === Application::STATUS_UNDER_REVIEW)
                    ->form([
                        Forms\Components\Textarea::make('remarks')->label('Remarks')->placeholder('Optional remarks'),
                    ])
                    ->action(fn (Application $record, array $data) =>
                        app(AdmissionService::class)->approve($record, auth()->user(), $data['remarks'] ?? null)
                    ),

                Actions\Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Application $record) => $record->application_status === Application::STATUS_UNDER_REVIEW)
                    ->form([
                        Forms\Components\Textarea::make('remarks')->label('Remarks')->required(),
                    ])
                    ->action(fn (Application $record, array $data) =>
                        app(AdmissionService::class)->reject($record, auth()->user(), $data['remarks'])
                    ),

                Actions\Action::make('requestCorrection')
                    ->label('Request Correction')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Application $record) => $record->application_status === Application::STATUS_UNDER_REVIEW)
                    ->form([
                        Forms\Components\Textarea::make('remarks')->label('Correction Notes')->required(),
                    ])
                    ->action(fn (Application $record, array $data) =>
                        app(AdmissionService::class)->requestCorrection($record, auth()->user(), $data['remarks'])
                    ),
            ])
            ->label('Workflow Actions')
            ->icon('heroicon-o-check-circle'),
        ];
    }
}
