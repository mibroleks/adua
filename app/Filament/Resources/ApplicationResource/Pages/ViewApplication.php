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

Provides:
- Premium application identity hero widget
- Full-width application hero layout
- Organised dossier export actions (Print, PDF, Excel, CSV)
- Organised admission workflow actions
- Clear confirmation and modal presentation
- Status-aware workflow action visibility

Design:
- Premium institutional officer experience
- Filament-native actions and widgets
- Compatible with dynamic institutional theming

Status: 🚦 Integration / Hardening
Version: 2.6 (full-width application hero integration)

Important:
- Application creation and updates remain portal-controlled.
- AdmissionService remains responsible for workflow operations.
- Existing workflow conditions and service integrations are preserved.
- Hero widget is presentation-only and does not modify application status.
*/

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use App\Filament\Widgets\ApplicationHeroWidget;
use App\Models\Application;
use App\Services\AdmissionService;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    /*
    |--------------------------------------------------------------------------
    | APPLICATION HERO WIDGET
    |--------------------------------------------------------------------------
    |
    | Displays the application identity, applicant information,
    | programme information and current workflow progress.
    |
    | The widget receives the current application record.
    | It is strictly presentation-only.
    |
    */

    /**
     * Register the application hero widget above the dossier.
     */
    protected function getHeaderWidgets(): array
    {
        return [
            ApplicationHeroWidget::class,
        ];
    }

    /**
     * Use a single-column header widget layout.
     *
     * This prevents Filament from allocating the hero
     * to one half of a two-column widget grid.
     */
    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    /**
     * Pass the current application record to the hero widget.
     */
    protected function getHeaderWidgetsData(): array
    {
        return [
            'record' => $this->getRecord(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | HEADER ACTIONS
    |--------------------------------------------------------------------------
    |
    | 1. Dossier exports
    | 2. Admission workflow operations
    |
    | Keep workflow execution inside AdmissionService.
    |
    */

    protected function getHeaderActions(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | DOSSIER EXPORT ACTIONS
            |--------------------------------------------------------------------------
            |
            | Existing routes and export behaviour are preserved.
            |
            */

            ActionGroup::make([

                Actions\Action::make('print')
                    ->label('Print Application')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn () => route('application.print', $this->record))
                    ->openUrlInNewTab(),

                Actions\Action::make('pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn () => route('application.pdf', $this->record))
                    ->openUrlInNewTab(),

                Actions\Action::make('excel')
                    ->label('Export to Excel')
                    ->icon('heroicon-o-table-cells')
                    ->color('gray')
                    ->url(fn () => route('application.export.excel', $this->record)),

                Actions\Action::make('csv')
                    ->label('Export to CSV')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->url(fn () => route('application.export.csv', $this->record)),

            ])
                ->label('Dossier Exports')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray'),

            /*
            |--------------------------------------------------------------------------
            | ADMISSION WORKFLOW ACTIONS
            |--------------------------------------------------------------------------
            |
            | These actions delegate all application status changes to
            | AdmissionService.
            |
            | Existing visibility conditions, service methods and
            | remarks validation are preserved.
            |
            */

            ActionGroup::make([

                /*
                |--------------------------------------------------------------------------
                | START REVIEW
                |--------------------------------------------------------------------------
                */

                Actions\Action::make('startReview')
                    ->label('Start Review')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Start Application Review')
                    ->modalDescription(
                        'Confirm that you want to move this application into the review stage.'
                    )
                    ->modalSubmitActionLabel('Start Review')
                    ->visible(
                        fn (Application $record) =>
                            $record->application_status === Application::STATUS_SUBMITTED
                    )
                    ->action(
                        fn (Application $record) =>
                            app(AdmissionService::class)->startReview(
                                $record,
                                auth()->id()
                            )
                    ),

                /*
                |--------------------------------------------------------------------------
                | APPROVE APPLICATION
                |--------------------------------------------------------------------------
                */

                Actions\Action::make('approve')
                    ->label('Approve Application')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Application')
                    ->modalDescription(
                        'Confirm that this application has satisfied the required admission review conditions.'
                    )
                    ->modalSubmitActionLabel('Confirm Approval')
                    ->visible(
                        fn (Application $record) =>
                            $record->application_status === Application::STATUS_UNDER_REVIEW
                    )
                    ->form([
                        Forms\Components\Textarea::make('remarks')
                            ->label('Remarks')
                            ->placeholder('Enter optional approval remarks.')
                            ->rows(4),
                    ])
                    ->action(
                        fn (Application $record, array $data) =>
                            app(AdmissionService::class)->approve(
                                $record,
                                auth()->user(),
                                $data['remarks'] ?? null
                            )
                    ),

                /*
                |--------------------------------------------------------------------------
                | REJECT APPLICATION
                |--------------------------------------------------------------------------
                */

                Actions\Action::make('reject')
                    ->label('Reject Application')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Application')
                    ->modalDescription(
                        'Provide the reason for rejecting this application. The remarks are required.'
                    )
                    ->modalSubmitActionLabel('Confirm Rejection')
                    ->visible(
                        fn (Application $record) =>
                            $record->application_status === Application::STATUS_UNDER_REVIEW
                    )
                    ->form([
                        Forms\Components\Textarea::make('remarks')
                            ->label('Rejection Remarks')
                            ->placeholder('Explain why this application is being rejected.')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(
                        fn (Application $record, array $data) =>
                            app(AdmissionService::class)->reject(
                                $record,
                                auth()->user(),
                                $data['remarks']
                            )
                    ),

                /*
                |--------------------------------------------------------------------------
                | REQUEST CORRECTION
                |--------------------------------------------------------------------------
                */

                Actions\Action::make('requestCorrection')
                    ->label('Request Correction')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Request Application Correction')
                    ->modalDescription(
                        'Specify the information or documents the applicant needs to correct.'
                    )
                    ->modalSubmitActionLabel('Send Correction Request')
                    ->visible(
                        fn (Application $record) =>
                            $record->application_status === Application::STATUS_UNDER_REVIEW
                    )
                    ->form([
                        Forms\Components\Textarea::make('remarks')
                            ->label('Correction Notes')
                            ->placeholder('Clearly explain the corrections required from the applicant.')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(
                        fn (Application $record, array $data) =>
                            app(AdmissionService::class)->requestCorrection(
                                $record,
                                auth()->user(),
                                $data['remarks']
                            )
                    ),

            ])
                ->label('Workflow Actions')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('gray'),

        ];
    }
}
