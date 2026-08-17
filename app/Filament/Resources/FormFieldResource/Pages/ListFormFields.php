<?php

/*
Component: ListFormFields Page
File Path: app/Filament/Resources/FormFieldResource/Pages/ListFormFields.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament page for listing all dynamic application form fields.
Officers can view, search, and create new fields.

Status: ✅ Production Ready
Version: 1.0 (Filament v3 compatible)
*/

namespace App\Filament\Resources\FormFieldResource\Pages;

use App\Filament\Resources\FormFieldResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFormFields extends ListRecords
{
    protected static string $resource = FormFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
