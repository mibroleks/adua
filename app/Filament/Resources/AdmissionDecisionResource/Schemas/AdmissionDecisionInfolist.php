<?php

/*
Component: Admission Decision Infolist
File Path: app/Filament/Resources/AdmissionDecisionResource/Schemas/AdmissionDecisionInfolist.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Presents a premium, structured and read-only institutional
admission decision record.

Responsibilities:
- Present both decision outcomes at a glance.
- Keep application processing separate from admission.
- Identify the officers responsible for each decision.
- Display decision dates and remarks.
- Display the chronological institutional admission outcome history.
- Preserve an accessible audit-information section.
- Provide a consistent institutional dossier layout.

Architecture:
- Application decisions and admission outcomes remain separate.
- Application approval does not automatically mean admission.
- Admission outcome changes are recorded through AdmissionService.
- Historical decision records remain auditable.
- Presentation changes do not modify decision business logic.
- No direct mutation of admission decision records is introduced.

Version: 3.3
*/

namespace App\Filament\Resources\AdmissionDecisionResource\Schemas;

use App\Models\AdmissionDecision;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;

class AdmissionDecisionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            /*
            |--------------------------------------------------------------------------
            | Decision summary
            |--------------------------------------------------------------------------
            */

            Section::make('Decision at a Glance')
                ->description(
                    'A summary of the application assessment result and the institution’s actual admission outcome.'
                )
                ->icon('heroicon-o-scale')
                ->schema([

                    TextEntry::make('application.application_number')
                        ->label('Application Reference')
                        ->placeholder('Not available')
                        ->copyable()
                        ->weight('bold')
                        ->icon('heroicon-o-document-text')
                        ->extraAttributes([
                            'class' => 'admission-decision-info__reference',
                        ]),

                    TextEntry::make('decision')
                        ->label('Application Processing')
                        ->badge()
                        ->formatStateUsing(
                            fn (?string $state): string => match ($state) {
                                AdmissionDecision::DECISION_APPROVED => 'Approved',
                                AdmissionDecision::DECISION_REJECTED => 'Rejected',
                                default => $state ?? 'Not recorded',
                            }
                        )
                        ->color(fn (?string $state): string => match ($state) {
                            AdmissionDecision::DECISION_APPROVED => 'success',
                            AdmissionDecision::DECISION_REJECTED => 'danger',
                            default => 'gray',
                        }),

                    TextEntry::make('admission_status')
                        ->label('Institutional Admission')
                        ->badge()
                        ->formatStateUsing(
                            fn (?string $state): string => match ($state) {
                                AdmissionDecision::ADMISSION_STATUS_PENDING => 'Pending',
                                AdmissionDecision::ADMISSION_STATUS_ADMITTED => 'Admitted',
                                AdmissionDecision::ADMISSION_STATUS_NOT_ADMITTED => 'Not Admitted',
                                AdmissionDecision::ADMISSION_STATUS_WAITLISTED => 'Waitlisted',
                                AdmissionDecision::ADMISSION_STATUS_DEFERRED => 'Deferred',
                                default => 'Not yet decided',
                            }
                        )
                        ->color(fn (?string $state): string => match ($state) {
                            AdmissionDecision::ADMISSION_STATUS_ADMITTED => 'success',
                            AdmissionDecision::ADMISSION_STATUS_NOT_ADMITTED => 'danger',
                            AdmissionDecision::ADMISSION_STATUS_WAITLISTED => 'warning',
                            AdmissionDecision::ADMISSION_STATUS_DEFERRED => 'info',
                            AdmissionDecision::ADMISSION_STATUS_PENDING => 'gray',
                            default => 'gray',
                        })
                        ->weight('bold'),

                ])
                ->columns(3)
                ->extraAttributes([
                    'class' => 'admission-decision-info__section admission-decision-info__summary',
                ])
                ->columnSpanFull(),

            /*
            |--------------------------------------------------------------------------
            | Application processing record
            |--------------------------------------------------------------------------
            */

            Section::make('Application Processing Record')
                ->description(
                    'The result of the application review and processing workflow, including the responsible processing officer.'
                )
                ->icon('heroicon-o-clipboard-document-check')
                ->schema([

                    TextEntry::make('decision')
                        ->label('Processing Outcome')
                        ->badge()
                        ->formatStateUsing(
                            fn (?string $state): string => match ($state) {
                                AdmissionDecision::DECISION_APPROVED => 'Approved',
                                AdmissionDecision::DECISION_REJECTED => 'Rejected',
                                default => $state ?? 'Not recorded',
                            }
                        )
                        ->color(fn (?string $state): string => match ($state) {
                            AdmissionDecision::DECISION_APPROVED => 'success',
                            AdmissionDecision::DECISION_REJECTED => 'danger',
                            default => 'gray',
                        })
                        ->weight('bold'),

                    TextEntry::make('officer.name')
                        ->label('Processing Officer')
                        ->placeholder('Not assigned')
                        ->icon('heroicon-o-user')
                        ->weight('medium'),

                    TextEntry::make('decided_at')
                        ->label('Processing Decision Date')
                        ->dateTime('d F Y, h:i A')
                        ->placeholder('Not recorded')
                        ->icon('heroicon-o-calendar'),

                    TextEntry::make('remarks')
                        ->label('Processing Remarks')
                        ->placeholder('No application processing remarks recorded.')
                        ->columnSpanFull()
                        ->extraAttributes([
                            'class' => 'admission-decision-info__remarks',
                        ]),

                ])
                ->columns(2)
                ->extraAttributes([
                    'class' => 'admission-decision-info__section admission-decision-info__processing',
                ])
                ->columnSpanFull(),

            /*
            |--------------------------------------------------------------------------
            | Institutional admission record
            |--------------------------------------------------------------------------
            */

            Section::make('Institutional Admission Record')
                ->description(
                    'The institution’s actual admission outcome, the officer responsible and any recorded institutional remarks.'
                )
                ->icon('heroicon-o-academic-cap')
                ->schema([

                    TextEntry::make('admission_status')
                        ->label('Admission Outcome')
                        ->badge()
                        ->formatStateUsing(
                            fn (?string $state): string => match ($state) {
                                AdmissionDecision::ADMISSION_STATUS_PENDING => 'Pending',
                                AdmissionDecision::ADMISSION_STATUS_ADMITTED => 'Admitted',
                                AdmissionDecision::ADMISSION_STATUS_NOT_ADMITTED => 'Not Admitted',
                                AdmissionDecision::ADMISSION_STATUS_WAITLISTED => 'Waitlisted',
                                AdmissionDecision::ADMISSION_STATUS_DEFERRED => 'Deferred',
                                default => 'Not yet decided',
                            }
                        )
                        ->color(fn (?string $state): string => match ($state) {
                            AdmissionDecision::ADMISSION_STATUS_ADMITTED => 'success',
                            AdmissionDecision::ADMISSION_STATUS_NOT_ADMITTED => 'danger',
                            AdmissionDecision::ADMISSION_STATUS_WAITLISTED => 'warning',
                            AdmissionDecision::ADMISSION_STATUS_DEFERRED => 'info',
                            AdmissionDecision::ADMISSION_STATUS_PENDING => 'gray',
                            default => 'gray',
                        })
                        ->weight('bold'),

                    TextEntry::make('admissionOfficer.name')
                        ->label('Admission Officer')
                        ->placeholder('Not yet assigned')
                        ->icon('heroicon-o-user')
                        ->weight('medium'),

                    TextEntry::make('admission_decided_at')
                        ->label('Admission Decision Date')
                        ->dateTime('d F Y, h:i A')
                        ->placeholder('Not yet decided')
                        ->icon('heroicon-o-calendar'),

                    TextEntry::make('admission_remarks')
                        ->label('Institutional Admission Remarks')
                        ->placeholder('No institutional admission remarks recorded.')
                        ->columnSpanFull()
                        ->extraAttributes([
                            'class' => 'admission-decision-info__remarks',
                        ]),

                ])
                ->columns(2)
                ->extraAttributes([
                    'class' => 'admission-decision-info__section admission-decision-info__admission',
                ])
                ->columnSpanFull(),

            /*
            |--------------------------------------------------------------------------
            | Institutional admission outcome history
            |--------------------------------------------------------------------------
            |
            | This section reads the admissionHistories relationship.
            | The relationship already sorts entries newest first.
            |
            | History is read-only. All outcome changes remain controlled
            | by AdmissionService.
            |
            */

            Section::make('Admission Outcome History')
                ->description(
                    'A chronological audit trail of institutional admission outcome changes, including the previous outcome, new outcome, responsible officer and recorded remarks.'
                )
                ->icon('heroicon-o-clock')
                ->schema([

                    RepeatableEntry::make('admissionHistories')
                        ->label('Outcome Changes')
                        ->placeholder(
                            'No admission outcome history has been recorded.'
                        )
                        ->schema([

                            TextEntry::make('old_status')
                                ->label('Previous Outcome')
                                ->badge()
                                ->formatStateUsing(
                                    fn (?string $state): string => match ($state) {
                                        AdmissionDecision::ADMISSION_STATUS_PENDING => 'Pending',
                                        AdmissionDecision::ADMISSION_STATUS_ADMITTED => 'Admitted',
                                        AdmissionDecision::ADMISSION_STATUS_NOT_ADMITTED => 'Not Admitted',
                                        AdmissionDecision::ADMISSION_STATUS_WAITLISTED => 'Waitlisted',
                                        AdmissionDecision::ADMISSION_STATUS_DEFERRED => 'Deferred',
                                        null => 'Initial Record',
                                        default => $state,
                                    }
                                )
                                ->color(fn (?string $state): string => match ($state) {
                                    AdmissionDecision::ADMISSION_STATUS_ADMITTED => 'success',
                                    AdmissionDecision::ADMISSION_STATUS_NOT_ADMITTED => 'danger',
                                    AdmissionDecision::ADMISSION_STATUS_WAITLISTED => 'warning',
                                    AdmissionDecision::ADMISSION_STATUS_DEFERRED => 'info',
                                    AdmissionDecision::ADMISSION_STATUS_PENDING => 'gray',
                                    default => 'gray',
                                }),

                            TextEntry::make('new_status')
                                ->label('New Outcome')
                                ->badge()
                                ->formatStateUsing(
                                    fn (?string $state): string => match ($state) {
                                        AdmissionDecision::ADMISSION_STATUS_PENDING => 'Pending',
                                        AdmissionDecision::ADMISSION_STATUS_ADMITTED => 'Admitted',
                                        AdmissionDecision::ADMISSION_STATUS_NOT_ADMITTED => 'Not Admitted',
                                        AdmissionDecision::ADMISSION_STATUS_WAITLISTED => 'Waitlisted',
                                        AdmissionDecision::ADMISSION_STATUS_DEFERRED => 'Deferred',
                                        default => $state ?? 'Not recorded',
                                    }
                                )
                                ->color(fn (?string $state): string => match ($state) {
                                    AdmissionDecision::ADMISSION_STATUS_ADMITTED => 'success',
                                    AdmissionDecision::ADMISSION_STATUS_NOT_ADMITTED => 'danger',
                                    AdmissionDecision::ADMISSION_STATUS_WAITLISTED => 'warning',
                                    AdmissionDecision::ADMISSION_STATUS_DEFERRED => 'info',
                                    AdmissionDecision::ADMISSION_STATUS_PENDING => 'gray',
                                    default => 'gray',
                                })
                                ->weight('bold'),

                            TextEntry::make('officer.name')
                                ->label('Responsible Officer')
                                ->placeholder('Officer record unavailable')
                                ->icon('heroicon-o-user'),

                            TextEntry::make('changed_at')
                                ->label('Date and Time')
                                ->dateTime('d F Y, h:i A')
                                ->placeholder('Date not recorded')
                                ->icon('heroicon-o-calendar'),

                            TextEntry::make('remarks')
                                ->label('Decision Remarks')
                                ->placeholder('No remarks recorded for this change.')
                                ->columnSpanFull()
                                ->extraAttributes([
                                    'class' => 'admission-decision-info__remarks',
                                ]),

                        ])
                        ->columns(2)
                        ->contained(true),

                ])
                ->collapsible()
                ->collapsed(false)
                ->extraAttributes([
                    'class' => 'admission-decision-info__section admission-decision-info__history',
                ])
                ->columnSpanFull(),

            /*
            |--------------------------------------------------------------------------
            | Audit information
            |--------------------------------------------------------------------------
            */

            Section::make('Record Audit Information')
                ->description(
                    'Administrative identifiers and timestamps associated with this admission decision record.'
                )
                ->icon('heroicon-o-clock')
                ->schema([

                    TextEntry::make('id')
                        ->label('Decision Record ID')
                        ->weight('medium')
                        ->copyable(),

                    TextEntry::make('created_at')
                        ->label('Record Created')
                        ->dateTime('d F Y, h:i A')
                        ->placeholder('Not available')
                        ->icon('heroicon-o-calendar'),

                    TextEntry::make('updated_at')
                        ->label('Last Updated')
                        ->dateTime('d F Y, h:i A')
                        ->placeholder('Not available')
                        ->icon('heroicon-o-arrow-path'),

                ])
                ->columns(3)
                ->collapsible()
                ->collapsed()
                ->extraAttributes([
                    'class' => 'admission-decision-info__section admission-decision-info__audit',
                ])
                ->columnSpanFull(),

        ]);
    }
}
