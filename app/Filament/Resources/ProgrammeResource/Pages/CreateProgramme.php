<?php

/*
Component: Create Programme Page
File Path: app/Filament/Resources/ProgrammeResource/Pages/CreateProgramme.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament admin page for creating new academic programmes.
Uses ProgrammeResource as the base resource.

Status: ✅ Production Ready
Version: 1.0 (Filament v3 compatible)
*/

namespace App\Filament\Resources\ProgrammeResource\Pages;

use App\Filament\Resources\ProgrammeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProgramme extends CreateRecord
{
    protected static string $resource = ProgrammeResource::class;
}
