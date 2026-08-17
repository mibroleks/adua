<?php

/*
Component: EditFormField Page
File Path: app/Filament/Resources/FormFieldResource/Pages/EditFormField.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament page for editing an existing dynamic application form field.
Officers can update label, key, type, required flag, options, and deactivate fields.

Status: ✅ Production Ready
Version: 1.0 (Filament v3 compatible)
*/

namespace App\Filament\Resources\FormFieldResource\Pages;

use App\Filament\Resources\FormFieldResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFormField extends EditRecord
{
    protected static string $resource = FormFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
