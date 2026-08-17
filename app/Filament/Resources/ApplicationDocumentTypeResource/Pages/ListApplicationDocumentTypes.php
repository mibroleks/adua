<?php

/*
Component: ListApplicationDocumentTypes Page
File Path: app/Filament/Resources/ApplicationDocumentTypeResource/Pages/ListApplicationDocumentTypes.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament page for listing all application document types.
Officers can view, search, filter, and create new document types.

Status: ✅ Production Ready
Version: 1.0 (Filament v3 compatible)
*/

namespace App\Filament\Resources\ApplicationDocumentTypeResource\Pages;

use App\Filament\Resources\ApplicationDocumentTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListApplicationDocumentTypes extends ListRecords
{
    protected static string $resource = ApplicationDocumentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
