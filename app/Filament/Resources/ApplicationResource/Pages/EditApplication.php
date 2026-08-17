<?php

/*
Component: Edit Application Page (Disabled)
File Path: app/Filament/Resources/ApplicationResource/Pages/EditApplication.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
This page is scaffolded by Filament but disabled in production.
Applications are created and updated via the applicant portal,
not manually by officers. Keeping the file avoids namespace errors
but ensures no edit/delete functionality is exposed.

Status: ✅ Disabled (namespace corrected)
Version: 1.7 (Filament v3 compatible)
*/

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use Filament\Resources\Pages\EditRecord;

class EditApplication extends EditRecord
{
    protected static string $resource = ApplicationResource::class;

    // Override to disable header actions
    protected function getHeaderActions(): array
    {
        return []; // no edit/delete actions
    }

    // Prevent manual editing by officers
    protected function mutateFormDataBeforeSave(array $data): array
    {
        abort(403, 'Manual application editing is disabled.');
    }

    // Prevent manual deletion by officers
    protected function beforeDelete(): void
    {
        abort(403, 'Manual application deletion is disabled.');
    }
}
