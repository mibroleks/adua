<?php

/*
Component: Application Resource
File Path: app/Filament/Resources/ApplicationResource.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides Filament admin interface for managing applications.
Applications are created via applicant portal, so officers can
list and view them, and manage decisions/documents. Manual create/edit
may be restricted depending on policy.

Status: ✅ Production Ready (namespace corrected)
Version: 1.8 (Filament v3 compatible, with relation manager registered)
*/

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages\CreateApplication;
use App\Filament\Resources\ApplicationResource\Pages\EditApplication;
use App\Filament\Resources\ApplicationResource\Pages\ListApplications;
use App\Filament\Resources\ApplicationResource\Pages\ViewApplication;
use App\Filament\Resources\ApplicationResource\Schemas\ApplicationForm;
use App\Filament\Resources\ApplicationResource\Schemas\ApplicationInfolist;
use App\Filament\Resources\ApplicationResource\Tables\ApplicationsTable;
use App\Filament\Resources\ApplicationResource\RelationManagers\DocumentsRelationManager;
use App\Models\Application;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    // Navigation settings
    public static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    public static UnitEnum|string|null $navigationGroup = 'Admissions';
    public static ?string $navigationLabel = 'Applications';

    // Title attribute for display
    protected static ?string $recordTitleAttribute = 'application_number';

    // ✅ Form definition (if officers can edit certain fields)
    public static function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    // ✅ Infolist definition (structured view)
    public static function infolist(Schema $schema): Schema
    {
        return ApplicationInfolist::configure($schema);
    }

    // ✅ Table definition (list view)
    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
    }

    // ✅ Register relation managers (Documents tab)
    public static function getRelations(): array
    {
        return [
            DocumentsRelationManager::class,
        ];
    }

    // ✅ Pages (List + View always; Create/Edit optional depending on policy)
    public static function getPages(): array
    {
        return [
            'index'  => ListApplications::route('/'),
            'view'   => ViewApplication::route('/{record}'),
            'create' => CreateApplication::route('/create'),
            'edit'   => EditApplication::route('/{record}/edit'),
        ];
    }
}
