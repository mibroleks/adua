<?php

/*
Component: EditApplicationDocumentType Page
File Path: app/Filament/Resources/ApplicationDocumentTypeResource/Pages/EditApplicationDocumentType.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament page for editing an existing application document type.
Officers can update name, key, required flag, allowed file types, max size, and active status.

Status: ✅ Production Ready
Version: 1.0 (Filament v3 compatible)
*/

namespace App\Filament\Resources\ApplicationDocumentTypeResource\Pages;

use App\Filament\Resources\ApplicationDocumentTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditApplicationDocumentType extends EditRecord
{
    protected static string $resource = ApplicationDocumentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
