<?php

/*
Component: Application Resource
File Path: app/Filament/Resources/ApplicationResource.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament interface for managing admission applications.

Applications are created through the applicant portal. Officers can
list, view, and manage applications, including their documents and
admission decisions, subject to the applicable policies.

Responsibilities:
- Register the Application model with Filament.
- Configure the application form and infolist.
- Use the shared ApplicationsTable configuration.
- Register application relation managers.
- Register application resource pages.

Integration:
- Supports referral partner attribution.
- Supports the ApplicationOverview KPI widget through the
  shared resource table configuration.
- Keeps referral attribution as informational marketing metadata.

Status: Updated for the Filament 5 application architecture.
Version: 1.9
*/

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages\CreateApplication;
use App\Filament\Resources\ApplicationResource\Pages\EditApplication;
use App\Filament\Resources\ApplicationResource\Pages\ListApplications;
use App\Filament\Resources\ApplicationResource\Pages\ViewApplication;
use App\Filament\Resources\ApplicationResource\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\ApplicationResource\Schemas\ApplicationForm;
use App\Filament\Resources\ApplicationResource\Schemas\ApplicationInfolist;
use App\Filament\Resources\ApplicationResource\Tables\ApplicationsTable;
use App\Models\Application;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */

    public static BackedEnum|string|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    public static UnitEnum|string|null $navigationGroup = 'Admissions';

    public static ?string $navigationLabel = 'Applications';

    protected static ?string $recordTitleAttribute = 'application_number';

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    public static function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    /*
    |--------------------------------------------------------------------------
    | Infolist
    |--------------------------------------------------------------------------
    */

    public static function infolist(Schema $schema): Schema
    {
        return ApplicationInfolist::configure($schema);
    }

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    |
    | Keep this connected to the shared ApplicationsTable configuration.
    | The referral-source column, filters, and table behaviour are defined
    | centrally rather than duplicated in this resource.
    |
    */

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
    }

    /*
    |--------------------------------------------------------------------------
    | Relation Managers
    |--------------------------------------------------------------------------
    */

    public static function getRelations(): array
    {
        return [
            DocumentsRelationManager::class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Resource Pages
    |--------------------------------------------------------------------------
    */

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),

            'view' => ViewApplication::route('/{record}'),

            'create' => CreateApplication::route('/create'),

            'edit' => EditApplication::route('/{record}/edit'),
        ];
    }
}
