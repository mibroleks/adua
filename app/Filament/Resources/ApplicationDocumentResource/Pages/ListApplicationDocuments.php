<?php

/*
Component: ListApplicationDocuments Page
File Path: app/Filament/Resources/ApplicationDocumentResource/Pages/ListApplicationDocuments.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the index page for application documents in Filament.
Supports listing, searching, filtering, and creating new records.

Status: ✅ Production Ready
Version: 1.2
*/

namespace App\Filament\Resources\ApplicationDocumentResource\Pages;

use App\Filament\Resources\ApplicationDocumentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ActionGroup;
use App\Filament\Exports\ApplicationDocumentExporter; // ✅ new exporter class

class ListApplicationDocuments extends ListRecords
{
    protected static string $resource = ApplicationDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                CreateAction::make()
                    ->label('Upload Document'),
                ExportAction::make()
                    ->label('Export Documents')
                    ->exporter(ApplicationDocumentExporter::class), // ✅ required
            ]),
        ];
    }
}
