<?php

/*
Component: Create Setting Page (Disabled)
File Path: app/Filament/Resources/SettingResource/Pages/CreateSetting.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Originally intended to provide the Filament admin page for creating new settings.
Hardened architecture disables creation to prevent arbitrary keys being added.

Architecture:
- Officers cannot add new settings.
- Settings are seeded and predefined.
- Only edit/view allowed in SettingResource.

Status: ✅ Production Ready
Version: 1.2 (Filament v5 compatible, corrected visibility, hardened safety)
*/

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;

    /**
     * Hardened: disable creation entirely.
     */
    public function canCreateAnother(): bool
    {
        return false;
    }

    protected function authorizeAccess(): void
    {
        // ❌ Prevent access to this page
        abort(403, 'Creating new settings is not allowed.');
    }
}
