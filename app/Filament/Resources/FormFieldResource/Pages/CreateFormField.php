<?php

/*
Component: CreateFormField Page
File Path: app/Filament/Resources/FormFieldResource/Pages/CreateFormField.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament page for creating a new dynamic application form field.
Officers can define label, key, type, required flag, and options.

Status: ✅ Production Ready
Version: 1.0 (Filament v3 compatible)
*/

namespace App\Filament\Resources\FormFieldResource\Pages;

use App\Filament\Resources\FormFieldResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFormField extends CreateRecord
{
    protected static string $resource = FormFieldResource::class;
}
