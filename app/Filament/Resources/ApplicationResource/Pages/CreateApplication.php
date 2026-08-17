<?php

/*
Component: Create Application Page (Disabled)
File Path: app/Filament/Resources/ApplicationResource/Pages/CreateApplication.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
This page is scaffolded by Filament but disabled in production.
Applications are created via the applicant portal, not manually
by officers. Keeping the file avoids namespace errors but ensures
no create functionality is exposed.

Status: ✅ Disabled (namespace corrected)
Version: 1.7 (Filament v3 compatible)
*/

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;

    // Override to disable creation
    protected function getHeaderActions(): array
    {
        return []; // no actions
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Prevent manual creation by officers
        abort(403, 'Manual application creation is disabled.');
    }
}
