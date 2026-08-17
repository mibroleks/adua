<?php

/*
Component: ViewFormField Page
File Path: app/Filament/Resources/FormFieldResource/Pages/ViewFormField.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament page for viewing a single dynamic application form field.
Officers can inspect details and quickly edit the field if needed.

Status: ✅ Production Ready
Version: 1.0 (Filament v3 compatible)
*/

namespace App\Filament\Resources\FormFieldResource\Pages;

use App\Filament\Resources\FormFieldResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFormField extends ViewRecord
{
    protected static string $resource = FormFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
