<?php

/*
Component: Admission Decision Form
File Path: app/Filament/Resources/AdmissionDecisionResource/Schemas/AdmissionDecisionForm.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides a structured institutional form for recording
application-processing decisions.

Architecture:
- Application decisions remain separate from admission outcomes.
- Only APPROVED and REJECTED are selectable here.
- Institutional admission outcomes are managed through
  AdmissionService, not this form.

Version: 2.0
*/

namespace App\Filament\Resources\AdmissionDecisionResource\Schemas;

use App\Models\AdmissionDecision;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;

class AdmissionDecisionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Application Processing Decision')
                ->description(
                    'Record the outcome of the application review. This decision determines whether the application is approved or rejected for further institutional admission consideration.'
                )
                ->icon('heroicon-o-clipboard-document-check')
                ->schema([

                    Select::make('decision')
                        ->label('Application Decision')
                        ->placeholder('Select application decision')
                        ->options([
                            AdmissionDecision::DECISION_APPROVED => 'Approved',
                            AdmissionDecision::DECISION_REJECTED => 'Rejected',
                        ])
                        ->required()
                        ->native(false)
                        ->searchable()
                        ->helperText(
                            'Approval confirms that the application has passed processing. It does not automatically grant admission.'
                        )
                        ->columnSpanFull(),

                    Textarea::make('remarks')
                        ->label('Processing Remarks')
                        ->placeholder(
                            'Enter the reason, assessment or relevant observations supporting this application decision...'
                        )
                        ->rows(5)
                        ->maxLength(1000)
                        ->autosize()
                        ->columnSpanFull()
                        ->helperText(
                            'Optional. Maximum 1,000 characters.'
                        ),

                ])
                ->columns(1)
                ->columnSpanFull(),

        ]);
    }
}
