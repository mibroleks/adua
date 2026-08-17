<?php

/*
Component: Edit Programme Page
File Path: app/Filament/Resources/ProgrammeResource/Pages/EditProgramme.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament admin page for editing existing academic programmes.
Includes header actions for viewing and deleting a programme.

Status: ✅ Production Ready
Version: 1.0 (Filament v3 compatible)
*/

namespace App\Filament\Resources\ProgrammeResource\Pages;

use App\Filament\Resources\ProgrammeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProgramme extends EditRecord
{
    protected static string $resource = ProgrammeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
