<?php

/*
Component: ApplicationDocument Resource
File Path: app/Filament/Resources/ApplicationDocumentResource.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides Filament admin interface for managing student application documents.
Supports listing, viewing, editing, and verifying/rejecting uploaded documents.

Status: ✅ Production Ready
Version: 1.3
*/

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationDocumentResource\Pages;
use App\Filament\Resources\ApplicationDocumentResource\Schemas\ApplicationDocumentForm;
use App\Filament\Resources\ApplicationDocumentResource\Schemas\ApplicationDocumentInfolist;
use App\Filament\Resources\ApplicationDocumentResource\Tables\ApplicationDocumentsTable;
use App\Models\ApplicationDocument;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class ApplicationDocumentResource extends Resource
{
    /**
     * The underlying model.
     */
    protected static ?string $model = ApplicationDocument::class;

    /**
     * Navigation settings.
     * Filament v3 requires BackedEnum|string|null for $navigationIcon
     * and UnitEnum|string|null for $navigationGroup.
     */
    public static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';
    public static UnitEnum|string|null $navigationGroup = 'Admissions';

    /**
     * Record title attribute.
     */
    protected static ?string $recordTitleAttribute = 'original_name';

    /**
     * Form schema.
     */
    public static function form(Schema $schema): Schema
    {
        return ApplicationDocumentForm::configure($schema);
    }

    /**
     * Infolist schema.
     */
    public static function infolist(Schema $schema): Schema
    {
        return ApplicationDocumentInfolist::configure($schema);
    }

    /**
     * Table schema.
     */
    public static function table(Table $table): Table
    {
        return ApplicationDocumentsTable::configure($table);
    }

    /**
     * Relations.
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Pages.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListApplicationDocuments::route('/'),
            'create' => Pages\CreateApplicationDocument::route('/create'),
            'view'   => Pages\ViewApplicationDocument::route('/{record}'),
            'edit'   => Pages\EditApplicationDocument::route('/{record}/edit'),
        ];
    }
}
