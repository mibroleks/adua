<?php

/*
Component: List Settings Page (Hardened)
File Path: app/Filament/Resources/SettingResource/Pages/ListSettings.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament admin page for listing all settings.

Architecture:
- Officers can view all settings in a table.
- No create/delete actions allowed.
- Uses SettingResource for schema and configuration.

Status: ✅ Production Ready
Version: 1.1 (Filament v5 compatible, corrected imports, hardened actions)
*/

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Resources\Pages\ListRecords;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    /**
     * Header actions available on the list page.
     * Hardened: no create/delete actions.
     */
    protected function getHeaderActions(): array
    {
        return [
            // ❌ CreateAction removed for safety
            // Officers can only view/edit existing settings
        ];
    }
}
