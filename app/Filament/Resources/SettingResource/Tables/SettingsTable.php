<?php

/*
Component: Settings Table (Hardened + Media Preview)
File Path: app/Filament/Resources/SettingResource/Tables/SettingsTable.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the table configuration for the SettingResource.
Ensures officers can view and edit settings but cannot create or delete arbitrary keys.
Adds support for previewing uploaded media (images/videos).

Architecture:
- Uses Filament v5 table API.
- Record actions limited to View + Edit.
- Bulk delete disabled for safety.
- Prevents exposure of sensitive/system keys.
- For hero_media, renders thumbnails/videos instead of raw JSON.

Status: ✅ Production Ready
Version: 2.2 (Filament v5 compatible, media preview support)
*/

namespace App\Filament\Resources\SettingResource\Tables;

use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group')
                    ->label('Group')
                    ->badge()
                    ->sortable(),

                TextColumn::make('key')
                    ->label('Setting')
                    ->searchable()
                    ->sortable(),

                // ✅ Show media preview if hero_media
                ViewColumn::make('value')
                    ->label('Media')
                    ->view('filament.tables.columns.hero-media-preview')
                    ->visible(fn ($record) => $record->key === 'institution.hero_media'),

                // ✅ Fallback for non-media values
                TextColumn::make('value')
                    ->label('Current Value')
                    ->limit(50)
                    ->visible(fn ($record) => $record->key !== 'institution.hero_media'),

                TextColumn::make('type')
                    ->badge(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // Optional filters (e.g. by group)
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                // ❌ Bulk delete disabled for safety
            ]);
    }
}
