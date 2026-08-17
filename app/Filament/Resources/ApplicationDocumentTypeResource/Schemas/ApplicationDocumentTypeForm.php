<?php

/*
Component: ApplicationDocumentTypeForm Schema
File Path: app/Filament/Resources/ApplicationDocumentTypeResource/Schemas/ApplicationDocumentTypeForm.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the Filament form schema for managing application document types.
Supports name, key, required flag, allowed file types, max size, and active status.

Status: ✅ Production Ready
Version: 1.1 (Filament v3 compatible)
*/

namespace App\Filament\Resources\ApplicationDocumentTypeResource\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;

class ApplicationDocumentTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('name')
                ->label('Document Name')
                ->required(),

            Forms\Components\TextInput::make('key')
                ->label('Key')
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\Toggle::make('required')
                ->label('Required'),

            Forms\Components\TextInput::make('allowed_file_types')
                ->label('Allowed File Types')
                ->helperText('Comma-separated list, e.g. jpg,png,pdf'),

            Forms\Components\TextInput::make('max_size')
                ->label('Max Size (KB)')
                ->numeric()
                ->helperText('Maximum file size allowed'),

            Forms\Components\Toggle::make('active')
                ->label('Active'),
        ]);
    }
}
