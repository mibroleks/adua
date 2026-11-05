<?php

/*
Component: View Admission Decision Page
File Path: app/Filament/Resources/AdmissionDecisionResource/Pages/ViewAdmissionDecision.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides a premium institutional workspace for viewing application
processing decisions and recording actual admission outcomes.

Responsibilities:
- Display the Admission Decision Hero widget.
- Display application-processing decisions separately.
- Display institutional admission outcomes separately.
- Render the dedicated read-only AdmissionDecisionInfolist.
- Provide visible institutional workflow actions.
- Capture admission decision remarks.
- Explain the implication of each selectable admission outcome.
- Preserve the admission decision audit trail.
- Prevent unrestricted editing of decision records.

Architecture:
- Application approval does not automatically mean admission.
- Only approved applications can receive an admission outcome.
- Admission outcomes are recorded through AdmissionService.
- AdmissionService maintains admission decision history.
- Admission letters remain a separate process.
- Generic editing and deletion are not permitted.
- The View page uses a dedicated read-only Infolist.
- Selectable admission outcomes are restricted to the authorised action modal.

Version: 2.7
*/

namespace App\Filament\Resources\AdmissionDecisionResource\Pages;

use App\Filament\Resources\AdmissionDecisionResource;
use App\Filament\Resources\AdmissionDecisionResource\Schemas\AdmissionDecisionInfolist;
use App\Filament\Widgets\AdmissionDecisionHeroWidget;
use App\Models\AdmissionDecision;
use App\Models\User;
use App\Services\AdmissionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;



use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewAdmissionDecision extends ViewRecord
{
    protected static string $resource = AdmissionDecisionResource::class;

    /*
    |--------------------------------------------------------------------------
    | Read-only decision presentation
    |--------------------------------------------------------------------------
    */

    public function infolist(Schema $schema): Schema
    {
        return AdmissionDecisionInfolist::configure($schema);
    }

    /*
    |--------------------------------------------------------------------------
    | Header widgets
    |--------------------------------------------------------------------------
    */

    protected function getHeaderWidgets(): array
    {
        return [
            AdmissionDecisionHeroWidget::class,
        ];
    }

    /**
     * Configure the header widget layout.
     *
     * Filament's parent Page class declares this method public,
     * so the child implementation must also be public.
     */
    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getHeaderWidgetsData(): array
    {
        return [
            'record' => $this->getRecord(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Header actions
    |--------------------------------------------------------------------------
    */

    protected function getHeaderActions(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Return to admission decisions
            |--------------------------------------------------------------------------
            */

            Action::make('backToAdmissionDecisions')
                ->label('All Admission Decisions')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->button()
                ->url(
                    fn (): string => AdmissionDecisionResource::getUrl('index')
                ),

            /*
            |--------------------------------------------------------------------------
            | Record or update institutional admission outcome
            |--------------------------------------------------------------------------
            */

            Action::make('recordAdmissionOutcome')
                ->label(
                    fn (AdmissionDecision $record): string =>
                        $record->admission_status === AdmissionDecision::ADMISSION_STATUS_PENDING
                            ? 'Record Admission Outcome'
                            : 'Update Admission Outcome'
                )
                ->icon('heroicon-o-academic-cap')
                ->color('success')
                ->size('lg')
                ->button()
                ->extraAttributes([
                    'class' => 'admission-outcome-primary-action',
                ])
                ->tooltip(
                    'Record or update the institution’s actual admission decision.'
                )
                ->visible(
                    fn (): bool => $this->canRecordAdmissionOutcome()
                )
                ->modalWidth('2xl')
                ->modalHeading('Institutional Admission Decision')
                ->modalDescription(
                    'Select the institution’s actual admission outcome. Read the explanation displayed beneath the selector before saving. This outcome is separate from application approval, and recording it does not automatically issue an admission letter.'
                )
                ->modalSubmitActionLabel('Save Admission Outcome')
                ->fillForm(
                    fn (AdmissionDecision $record): array => [
                        'admission_status' =>
                            $record->admission_status === AdmissionDecision::ADMISSION_STATUS_PENDING
                                ? null
                                : $record->admission_status,

                        'admission_remarks' => $record->admission_remarks,
                    ]
                )
                ->form([

                    Select::make('admission_status')
                        ->label('Institutional Admission Outcome')
                        ->placeholder('Select the institutional admission outcome')
                        ->options([
                            AdmissionDecision::ADMISSION_STATUS_ADMITTED => 'Admitted',
                            AdmissionDecision::ADMISSION_STATUS_NOT_ADMITTED => 'Not Admitted',
                            AdmissionDecision::ADMISSION_STATUS_WAITLISTED => 'Waitlisted',
                            AdmissionDecision::ADMISSION_STATUS_DEFERRED => 'Deferred',
                        ])
                        ->required()
                        ->native(false)
                        ->searchable()
                        ->live()
                        ->helperText(
                            fn (Get $get): string => match ($get('admission_status')) {

                                AdmissionDecision::ADMISSION_STATUS_ADMITTED =>
                                    'ADMITTED: The institution has offered the applicant admission. The applicant may proceed to the separate admission-letter and subsequent onboarding processes. Saving this outcome does not automatically issue an admission letter.',

                                AdmissionDecision::ADMISSION_STATUS_NOT_ADMITTED =>
                                    'NOT ADMITTED: The institution has decided not to offer this applicant admission for the current application. The applicant has not been admitted.',

                                AdmissionDecision::ADMISSION_STATUS_WAITLISTED =>
                                    'WAITLISTED: The applicant is placed on the institution’s reserve list. Admission may be offered later if a space becomes available and the institution approves it. This is not an admission offer.',

                                AdmissionDecision::ADMISSION_STATUS_DEFERRED =>
                                    'DEFERRED: The applicant’s admission consideration or offer is postponed to a later admission session. This does not mean the applicant has been admitted for the current session.',

                                default =>
                                    'Select an outcome to see its meaning and the implication for the applicant before saving.',
                            }
                        )
                        ->columnSpanFull(),

                    Textarea::make('admission_remarks')
                        ->label('Admission Decision Remarks')
                        ->placeholder(
                            'Provide the reason, explanation or relevant institutional remarks supporting this outcome...'
                        )
                        ->rows(5)
                        ->maxLength(1000)
                        ->autosize()
                        ->columnSpanFull()
                        ->helperText(
                            'Optional. These remarks will be stored with the admission outcome history. Maximum 1,000 characters.'
                        ),

                ])
                ->action(function (
                    array $data,
                    AdmissionDecision $record
                ): void {

                    $officer = auth()->user();

                    if (! $officer instanceof User) {
                        abort(
                            403,
                            'An authenticated officer is required to record an admission outcome.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Record outcome through AdmissionService
                    |--------------------------------------------------------------------------
                    */

                    app(AdmissionService::class)->recordAdmissionOutcome(
                        $record,
                        $officer,
                        $data['admission_status'],
                        $data['admission_remarks'] ?? null
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Refresh the displayed record
                    |--------------------------------------------------------------------------
                    */

                    $this->getRecord()->refresh();

                    Notification::make()
                        ->title('Admission outcome saved')
                        ->body(
                            'The institutional admission outcome and its history have been updated successfully.'
                        )
                        ->success()
                        ->send();
                }),

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Admission outcome eligibility
    |--------------------------------------------------------------------------
    */

    protected function canRecordAdmissionOutcome(): bool
    {
        $record = $this->getRecord();

        if (! $record instanceof AdmissionDecision) {
            return false;
        }

        if ($record->decision !== AdmissionDecision::DECISION_APPROVED) {
            return false;
        }

        $application = $record->application;

        if (! $application) {
            return false;
        }

        if ($application->application_status !== 'APPROVED') {
            return false;
        }

        return true;
    }
}
