<?php

/*
Component: FormField Resource
File Path: app/Filament/Resources/FormFieldResource.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides Filament admin interface for managing dynamic application form fields.
Officers can create, edit, view, and deactivate fields such as text inputs,
select options, checkboxes, etc.

Architecture:
- Fields are never hardcoded.
- Officers manage them via Filament.
- Supports label, type, required flag, and options.

Status: ✅ Production Ready
Version: 1.1 (Filament v3 compatible)
*/

namespace App\Filament\Resources;

use App\Models\FormField;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use BackedEnum;
use UnitEnum;

class FormFieldResource extends Resource
{
    protected static ?string $model = FormField::class;

    // Title attribute for display
    protected static ?string $recordTitleAttribute = 'label';

    // Navigation settings (must be public static in Filament v3)
    public static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-group';
    public static UnitEnum|string|null $navigationGroup = 'Admissions';
    protected static ?string $navigationLabel = 'Form Fields';

    // ✅ Form definition
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('label')
                ->label('Field Label')
                ->required(),

            Forms\Components\TextInput::make('key')
                ->label('Field Key')
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\Select::make('type')
                ->label('Field Type')
                ->required()
                ->options([
                    'text' => 'Text',
                    'textarea' => 'Textarea',
                    'number' => 'Number',
                    'date' => 'Date',
                    'select' => 'Select',
                    'checkbox' => 'Checkbox',
                ]),

            Forms\Components\Toggle::make('required')
                ->label('Required'),

            Forms\Components\Textarea::make('options')
                ->label('Options (comma separated)')
                ->helperText('Only used for select/checkbox fields'),

            Forms\Components\Toggle::make('active')
                ->label('Active')
                ->default(true),
        ]);
    }

    // ✅ Table definition
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('label')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('key')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('type')
                ->sortable(),

            Tables\Columns\IconColumn::make('required')
                ->boolean()
                ->label('Required'),

            Tables\Columns\IconColumn::make('active')
                ->boolean()
                ->label('Active'),

            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->label('Last Updated')
                ->sortable(),
        ]);
    }

    // ✅ Pages
    public static function getPages(): array
    {
        return [
            'index' => FormFieldResource\Pages\ListFormFields::route('/'),
            'create' => FormFieldResource\Pages\CreateFormField::route('/create'),
            'view' => FormFieldResource\Pages\ViewFormField::route('/{record}'),
            'edit' => FormFieldResource\Pages\EditFormField::route('/{record}/edit'),
        ];
    }
}
