<?php

/*
Component: ApplicationDocumentType Resource
File Path: app/Filament/Resources/ApplicationDocumentTypeResource.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides Filament admin interface for managing required or optional application documents.
Officers can define document name, key, required flag, allowed file types, maximum size, and active status.

Architecture:
- Documents are never hardcoded.
- Officers manage them via Filament.
- Supports name, key, required flag, allowed file types, max size, and active status.

Status: ✅ Production Ready
Version: 1.1 (Filament v3 compatible)
*/

namespace App\Filament\Resources;

use App\Models\ApplicationDocumentType;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\ApplicationDocumentTypeResource\Pages\ListApplicationDocumentTypes;
use App\Filament\Resources\ApplicationDocumentTypeResource\Pages\CreateApplicationDocumentType;
use App\Filament\Resources\ApplicationDocumentTypeResource\Pages\EditApplicationDocumentType;
use App\Filament\Resources\ApplicationDocumentTypeResource\Pages\ViewApplicationDocumentType;
use App\Filament\Resources\ApplicationDocumentTypeResource\Schemas\ApplicationDocumentTypeForm;
use App\Filament\Resources\ApplicationDocumentTypeResource\Schemas\ApplicationDocumentTypeInfolist;
use App\Filament\Resources\ApplicationDocumentTypeResource\Tables\ApplicationDocumentTypesTable;

class ApplicationDocumentTypeResource extends Resource
{
    protected static ?string $model = ApplicationDocumentType::class;

    // Navigation settings (must be public static in Filament v3)
    public static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    public static UnitEnum|string|null $navigationGroup = 'Admissions';
    protected static ?string $navigationLabel = 'Document Types';

    protected static ?string $recordTitleAttribute = 'name';

    // ✅ Form definition
    public static function form(Schema $schema): Schema
    {
        return ApplicationDocumentTypeForm::configure($schema);
    }

    // ✅ Infolist definition
    public static function infolist(Schema $schema): Schema
    {
        return ApplicationDocumentTypeInfolist::configure($schema);
    }

    // ✅ Table definition
    public static function table(Table $table): Table
    {
        return ApplicationDocumentTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    // ✅ Pages
    public static function getPages(): array
    {
        return [
            'index' => ListApplicationDocumentTypes::route('/'),
            'create' => CreateApplicationDocumentType::route('/create'),
            'view' => ViewApplicationDocumentType::route('/{record}'),
            'edit' => EditApplicationDocumentType::route('/{record}/edit'),
        ];
    }
}
