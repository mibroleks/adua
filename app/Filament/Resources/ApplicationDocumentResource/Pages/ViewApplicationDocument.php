<?php

/*
Component: ViewApplicationDocument Page
File Path: app/Filament/Resources/ApplicationDocumentResource/Pages/ViewApplicationDocument.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the view page for application documents in Filament.
Displays read-only details of the document, with quick access to edit if needed.

Status: ✅ Production Ready
Version: 1.1
*/

namespace App\Filament\Resources\ApplicationDocumentResource\Pages;

use App\Filament\Resources\ApplicationDocumentResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class ViewApplicationDocument extends ViewRecord
{
    protected static string $resource = ApplicationDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit Document')
                ->color('primary'),
            DeleteAction::make()
                ->label('Delete')
                ->color('danger'),
        ];
    }
}
