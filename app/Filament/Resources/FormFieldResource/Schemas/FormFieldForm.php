<?php

/*
Component: FormFieldForm Schema
File Path: app/Filament/Resources/FormFieldResource/Schemas/FormFieldForm.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the Filament form schema for managing dynamic application form fields.
Supports label, key, type, required flag, options, and active status.

Status: ✅ Production Ready
Version: 1.1 (Filament v3 compatible)
*/

namespace App\Filament\Resources\FormFieldResource\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;

class FormFieldForm
{
    public static function configure(Schema $schema): Schema
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
}
