<?php

/*
Component: ViewApplicationDocumentType Page
File Path: app/Filament/Resources/ApplicationDocumentTypeResource/Pages/ViewApplicationDocumentType.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament page for viewing details of a single application document type.
Officers can inspect attributes and quickly edit the document type if needed.

Status: ✅ Production Ready
Version: 1.0 (Filament v3 compatible)
*/

namespace App\Filament\Resources\ApplicationDocumentTypeResource\Pages;

use App\Filament\Resources\ApplicationDocumentTypeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewApplicationDocumentType extends ViewRecord
{
    protected static string $resource = ApplicationDocumentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
