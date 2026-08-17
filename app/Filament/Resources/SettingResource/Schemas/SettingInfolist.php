<?php

/*
Component: Setting Infolist Schema (Hardened + Media Preview)
File Path: app/Filament/Resources/SettingResource/Schemas/SettingInfolist.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the Filament infolist schema for displaying settings in read-only view mode.
Adds support for previewing uploaded media (images/videos).

Architecture:
- Officers can view a setting’s details clearly.
- Fields: key, value, type, group, timestamps.
- For hero_media, renders thumbnails/videos instead of raw JSON.
- Complements the ViewSetting page for inspection without editing.

Status: ✅ Production Ready
Version: 1.2 (Filament v5 compatible, media preview support)
*/

namespace App\Filament\Resources\SettingResource\Schemas;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;

class SettingInfolist
{
    public static function configure(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('key')
                    ->label('Key'),

                // ✅ Show media preview if hero_media
                ViewEntry::make('value')
                    ->label('Value')
                    ->view('filament.resources.setting-resource.pages.view-setting-media')
                    ->visible(fn ($record) => $record->key === 'institution.hero_media'),

                // ✅ Fallback for non-media values
                TextEntry::make('value')
                    ->label('Value')
                    ->visible(fn ($record) => $record->key !== 'institution.hero_media'),

                TextEntry::make('type')
                    ->label('Type'),

                TextEntry::make('group')
                    ->label('Group'),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->label('Created At'),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->label('Updated At'),
            ]);
    }
}
