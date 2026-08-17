<?php

/*
Component: AdmissionDecision Resource
File Path: app/Filament/Resources/AdmissionDecisionResource.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides Filament admin interface for managing admission decisions.
Officers can record decisions (APPROVED/REJECTED), add remarks, and view audit trail.

Status: ✅ Production Ready
Version: 1.2 (Filament v3 compatible)
*/

namespace App\Filament\Resources;

use App\Models\AdmissionDecision;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;

class AdmissionDecisionResource extends Resource
{
    protected static ?string $model = AdmissionDecision::class;

    // Icon for the sidebar
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-check-circle';

    // Group under which this resource appears in the sidebar
    protected static \UnitEnum|string|null $navigationGroup = 'Admissions';

    // Title attribute for records
    protected static ?string $recordTitleAttribute = 'id';

    // ✅ Form definition
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('application_id')
                ->relationship('application', 'application_number')
                ->required()
                ->label('Application'),

            Forms\Components\Select::make('officer_id')
                ->relationship('officer', 'name')
                ->required()
                ->label('Officer'),

            Forms\Components\Select::make('decision')
                ->options([
                    'APPROVED' => 'Approved',
                    'REJECTED' => 'Rejected',
                ])
                ->required()
                ->label('Decision'),

            Forms\Components\Textarea::make('remarks')
                ->label('Remarks')
                ->maxLength(1000),
        ]);
    }

    // ✅ Table definition
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('application.application_number')
                ->label('Application')
                ->searchable(),

            Tables\Columns\TextColumn::make('officer.name')
                ->label('Officer')
                ->searchable(),

            Tables\Columns\BadgeColumn::make('decision')
                ->colors([
                    'success' => 'APPROVED',
                    'danger' => 'REJECTED',
                ])
                ->label('Decision'),

            Tables\Columns\TextColumn::make('remarks')
                ->label('Remarks')
                ->limit(50),

            Tables\Columns\TextColumn::make('decided_at')
                ->dateTime()
                ->label('Decided At'),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => AdmissionDecisionResource\Pages\ListAdmissionDecisions::route('/'),
            'create' => AdmissionDecisionResource\Pages\CreateAdmissionDecision::route('/create'),
            'view' => AdmissionDecisionResource\Pages\ViewAdmissionDecision::route('/{record}'),
            'edit' => AdmissionDecisionResource\Pages\EditAdmissionDecision::route('/{record}/edit'),
        ];
    }
}
