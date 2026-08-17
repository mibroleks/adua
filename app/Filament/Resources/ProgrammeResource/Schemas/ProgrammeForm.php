<?php

/*
Component: Programme Form
File Path: app/Filament/Resources/ProgrammeResource/Schemas/ProgrammeForm.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the form schema for creating and editing programmes in Filament.
Supports name, code, description, degree type, duration, fee, active status,
admissions toggle, and academic hierarchy (faculty + department).

Status: ✅ Production Ready
Version: 1.4 (Filament v3 compatible, synced with migration/model)
*/

namespace App\Filament\Resources\ProgrammeResource\Schemas;

use App\Models\Faculty;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;

class ProgrammeForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([
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
                ->label('Programme Name')
                ->required(),

            Forms\Components\TextInput::make('code')
                ->label('Programme Code')
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\Textarea::make('description')
                ->label('Description'),

            Forms\Components\TextInput::make('degree_type')
                ->label('Degree Type')
                ->required(),

            Forms\Components\TextInput::make('duration')
                ->label('Duration (years)')
                ->numeric()
                ->default(4),

            Forms\Components\TextInput::make('application_fee')
                ->label('Application Fee (₦)')
                ->numeric()
                ->required(),

            Forms\Components\Textarea::make('requirements')
                ->label('Admission Requirements'),

            Forms\Components\Textarea::make('career_paths')
                ->label('Career Opportunities'),

            Forms\Components\Textarea::make('scholarships')
                ->label('Scholarships / Funding'),

            Forms\Components\TextInput::make('accreditation_body')
                ->label('Accreditation Body'),

            Forms\Components\TextInput::make('tuition')
                ->label('Tuition Fee (₦)')
                ->numeric()
                ->nullable(),

            Forms\Components\TextInput::make('credits')
                ->label('Total Credits')
                ->numeric()
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
}
