<?php

/*
Component: AdmissionDecision Resource
File Path: app/Filament/Resources/AdmissionDecisionResource.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament administrative interface for viewing
application-processing decisions and institutional admission outcomes.

Responsibilities:
- Display application approval and rejection decisions.
- Display institutional admission outcomes separately.
- Identify officers responsible for each decision.
- Provide access to individual admission decision records.
- Prevent unrestricted creation, editing and deletion.
- Route admission outcome changes through AdmissionService.
- Present individual records through a dedicated read-only Infolist.

Architecture:
- Application approval does not automatically mean admission.
- Application decisions and admission outcomes remain separate.
- Decision records originate from the application-processing workflow.
- Admission outcome changes must be recorded through AdmissionService.
- Historical decision records must be preserved.
- Admission letters are handled through a separate process.
- The dedicated Infolist is the authoritative read-only presentation.
- Generic resource CRUD operations remain restricted.
- Register presentation is delegated to AdmissionDecisionsTable.

Status: Premium presentation integration
Version: 2.4 (Filament 5)
*/

namespace App\Filament\Resources;

use App\Filament\Resources\AdmissionDecisionResource\Schemas\AdmissionDecisionInfolist;
use App\Filament\Resources\AdmissionDecisionResource\Tables\AdmissionDecisionsTable;
use App\Models\AdmissionDecision;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class AdmissionDecisionResource extends Resource
{
    /*
    |--------------------------------------------------------------------------
    | Resource configuration
    |--------------------------------------------------------------------------
    */

    protected static ?string $model = AdmissionDecision::class;

    protected static \BackedEnum|string|null $navigationIcon =
        'heroicon-o-check-circle';

    protected static \UnitEnum|string|null $navigationGroup =
        'Admissions';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Admission Decisions';

    protected static ?string $modelLabel = 'Admission Decision';

    protected static ?string $pluralModelLabel = 'Admission Decisions';

    /*
    |--------------------------------------------------------------------------
    | Resource access control
    |--------------------------------------------------------------------------
    |
    | Admission decision records must not be created, edited or deleted
    | through generic resource CRUD operations.
    |
    | The application-processing workflow creates its decision records.
    | The View page provides the controlled institutional admission
    | outcome workflow through AdmissionService.
    |
    */

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Resource form definition
    |--------------------------------------------------------------------------
    |
    | Retained for Filament resource compatibility.
    |
    | This form is not the intended presentation for viewing records.
    | Individual records are displayed through the dedicated Infolist
    | defined below.
    |
    | All fields remain disabled because generic editing is prohibited.
    |
    | Admission outcome changes must use AdmissionService.
    |
    */

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Section::make('Application Processing Decision')
                ->description(
                    'The application-processing outcome recorded by the institution. Application approval does not automatically grant admission.'
                )
                ->icon('heroicon-o-clipboard-document-check')
                ->schema([

                    Forms\Components\Select::make('application_id')
                        ->relationship('application', 'application_number')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled()
                        ->label('Application'),

                    Forms\Components\Select::make('officer_id')
                        ->relationship('officer', 'name')
                        ->searchable()
                        ->preload()
                        ->disabled()
                        ->label('Processing Officer'),

                    Forms\Components\Select::make('decision')
                        ->options([
                            AdmissionDecision::DECISION_APPROVED => 'Approved',
                            AdmissionDecision::DECISION_REJECTED => 'Rejected',
                        ])
                        ->disabled()
                        ->label('Application Decision'),

                    Forms\Components\Textarea::make('remarks')
                        ->label('Application Decision Remarks')
                        ->maxLength(1000)
                        ->rows(3)
                        ->disabled()
                        ->columnSpanFull(),

                    Forms\Components\DateTimePicker::make('decided_at')
                        ->label('Application Decision Date')
                        ->seconds(false)
                        ->disabled(),

                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Institutional Admission Outcome')
                ->description(
                    'The institution’s actual admission decision, recorded separately from application processing.'
                )
                ->icon('heroicon-o-academic-cap')
                ->schema([

                    Forms\Components\Placeholder::make('admission_status_display')
                        ->label('Admission Outcome')
                        ->content(
                            fn (?AdmissionDecision $record): string =>
                                $record?->admission_status
                                    ? str_replace(
                                        '_',
                                        ' ',
                                        $record->admission_status
                                    )
                                    : 'Not yet recorded'
                        ),

                    Forms\Components\Placeholder::make('admission_officer_display')
                        ->label('Admission Officer')
                        ->content(
                            fn (?AdmissionDecision $record): string =>
                                $record?->admissionOfficer?->name
                                    ?? 'Not yet assigned'
                        ),

                    Forms\Components\Placeholder::make('admission_decided_at_display')
                        ->label('Admission Decision Date')
                        ->content(
                            fn (?AdmissionDecision $record): string =>
                                $record?->admission_decided_at?->format(
                                    'd M Y, h:i A'
                                ) ?? 'Not yet decided'
                        ),

                    Forms\Components\Placeholder::make('admission_remarks_display')
                        ->label('Admission Outcome Remarks')
                        ->content(
                            fn (?AdmissionDecision $record): string =>
                                filled($record?->admission_remarks)
                                    ? $record->admission_remarks
                                    : 'No admission remarks recorded'
                        )
                        ->columnSpanFull(),

                ])
                ->columns(2)
                ->visible(fn (?AdmissionDecision $record): bool => $record !== null)
                ->columnSpanFull(),

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Dedicated read-only record presentation
    |--------------------------------------------------------------------------
    |
    | This is the authoritative presentation for an individual
    | admission decision record.
    |
    | Application processing and institutional admission outcomes
    | are displayed independently through AdmissionDecisionInfolist.
    |
    | This prevents the disabled resource form Select components
    | from being used as the View page's decision presentation.
    |
    */

    public static function infolist(Schema $schema): Schema
    {
        return AdmissionDecisionInfolist::configure($schema);
    }

    /*
    |--------------------------------------------------------------------------
    | Dedicated register table
    |--------------------------------------------------------------------------
    |
    | The admission decision register is configured separately in
    | AdmissionDecisionsTable.
    |
    | This keeps register columns, referral attribution, filters,
    | sorting and record actions outside the resource definition.
    |
    | The dedicated table must continue to distinguish:
    | - Application-processing decisions.
    | - Institutional admission outcomes.
    | - Original application referral attribution.
    |
    | Referral attribution is read from the related Application.
    | It is not duplicated or rewritten on AdmissionDecision.
    |
    */

    public static function table(Tables\Table $table): Tables\Table
    {
        return AdmissionDecisionsTable::configure($table);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public static function getRelations(): array
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Resource pages
    |--------------------------------------------------------------------------
    |
    | Only the register and individual record view are exposed.
    |
    | Generic Create and Edit routes are deliberately removed.
    |
    */

    public static function getPages(): array
    {
        return [
            'index' => AdmissionDecisionResource\Pages\ListAdmissionDecisions::route('/'),

            'view' => AdmissionDecisionResource\Pages\ViewAdmissionDecision::route('/{record}'),
        ];
    }
}
