<?php

/*
Component: CreateApplicationDocumentType Page
File Path: app/Filament/Resources/ApplicationDocumentTypeResource/Pages/CreateApplicationDocumentType.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament page for creating a new application document type.
Officers can define name, key, required flag, allowed file types, max size, and active status.

Status: ✅ Production Ready
Version: 1.0 (Filament v3 compatible)
*/

namespace App\Filament\Resources\ApplicationDocumentTypeResource\Pages;

use App\Filament\Resources\ApplicationDocumentTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateApplicationDocumentType extends CreateRecord
{
    protected static string $resource = ApplicationDocumentTypeResource::class;
}
