<?php

/*
Component: Programme Resource
File Path: app/Filament/Resources/ProgrammeResource.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides Filament admin interface for managing academic programmes.
Officers can create, edit, view, and deactivate programmes.

Architecture:
- Programmes are never hardcoded.
- Officers manage them via Filament.
- Supports name, code, description, degree type, duration, fee, status, and admissions toggle.
- Includes faculty and department dropdowns for hierarchy.

Status: ✅ Production Ready
Version: 1.6 (Filament v3 compatible, synced with migration/model)
*/

namespace App\Filament\Resources;

use App\Models\Programme;
use App\Models\Faculty;
use App\Models\Department;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use UnitEnum;
use BackedEnum;

class ProgrammeResource extends Resource
{
    protected static ?string $model = Programme::class;

    // Title attribute for display
    protected static ?string $recordTitleAttribute = 'name';

    // Navigation settings
    public static BackedEnum|string|null $navigationIcon = 'heroicon-o-academic-cap';
    public static UnitEnum|string|null $navigationGroup = 'Admissions';
    protected static ?string $navigationLabel = 'Programmes';

    // ✅ Form definition
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('faculty_id')
                ->label('Faculty')
                ->options(Faculty::pluck('name', 'id'))
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(fn (callable $set) => $set('department_id', null)),

            Forms\Components\Select::make('department_id')
                ->label('Department')
                ->options(fn (callable $get) => Department::where('faculty_id', $get('faculty_id'))
                    ->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('name')
                ->required()
                ->label('Programme Name'),

            Forms\Components\TextInput::make('code')
                ->required()
                ->unique(ignoreRecord: true)
                ->label('Programme Code'),

            Forms\Components\Textarea::make('description')
                ->label('Description'),

            Forms\Components\TextInput::make('degree_type')
                ->required()
                ->label('Degree Type'),

            Forms\Components\TextInput::make('duration')
                ->numeric()
                ->default(4)
                ->label('Duration (years)'),

            Forms\Components\TextInput::make('application_fee')
                ->numeric()
                ->required()
                ->label('Application Fee (₦)'),

            Forms\Components\Textarea::make('requirements')
                ->label('Admission Requirements'),

            Forms\Components\Textarea::make('career_paths')
                ->label('Career Opportunities'),

            Forms\Components\Textarea::make('scholarships')
                ->label('Scholarships / Funding'),

            Forms\Components\TextInput::make('accreditation_body')
                ->label('Accreditation Body'),

            Forms\Components\TextInput::make('tuition')
                ->numeric()
                ->label('Tuition Fee (₦)')
                ->nullable(),

            Forms\Components\TextInput::make('credits')
                ->numeric()
                ->label('Total Credits')
                ->nullable(),

            Forms\Components\TextInput::make('delivery_mode')
                ->label('Delivery Mode')
                ->nullable(),

            Forms\Components\TextInput::make('language')
                ->label('Language of Instruction')
                ->default('English')
                ->nullable(),

            Forms\Components\Textarea::make('outcomes')
                ->label('Programme Outcomes')
                ->nullable(),

            Forms\Components\Toggle::make('active')
                ->label('Active'),

            Forms\Components\Toggle::make('application_enabled')
                ->label('Admissions Enabled')
                ->default(true),
        ]);
    }

    // ✅ Table definition
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('degree_type'),
            Tables\Columns\TextColumn::make('duration'),
            Tables\Columns\TextColumn::make('application_fee')
                ->money('NGN', true)
                ->sortable()
                ->label('Application Fee'),
            Tables\Columns\TextColumn::make('tuition')
                ->money('NGN', true)
                ->sortable()
                ->label('Tuition')
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('department.name')->label('Department'),
            Tables\Columns\TextColumn::make('department.faculty.name')->label('Faculty'),
            Tables\Columns\IconColumn::make('active')->boolean(),
            Tables\Columns\IconColumn::make('application_enabled')->boolean()->label('Admissions Enabled'),
            Tables\Columns\TextColumn::make('updated_at')->dateTime(),
        ]);
    }

    // ✅ Pages
    public static function getPages(): array
    {
        return [
            'index' => ProgrammeResource\Pages\ListProgrammes::route('/'),
            'create' => ProgrammeResource\Pages\CreateProgramme::route('/create'),
            'view' => ProgrammeResource\Pages\ViewProgramme::route('/{record}'),
            'edit' => ProgrammeResource\Pages\EditProgramme::route('/{record}/edit'),
        ];
    }
}
