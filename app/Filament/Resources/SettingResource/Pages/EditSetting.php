<?php

/*
Component: Edit Setting Page (Hardened)
File Path: app/Filament/Resources/SettingResource/Pages/EditSetting.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament admin page for editing existing settings.

Architecture:
- Officers can edit and view settings via Filament.
- Delete action removed for safety.
- Uses SettingResource for schema and configuration.
- Ensures dynamic configuration values are updated without code changes.
- Optional Reset action can clear theme overrides back to preset defaults.

Status: ✅ Production Ready
Version: 1.4 (Filament v5 compatible, hardened actions, reset-ready, notifications fixed)
*/

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use App\Services\ThemeService;
use Filament\Notifications\Notification;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    /**
     * Header actions available on the edit page.
     * Hardened: only view allowed, delete removed.
     * Added Reset action for theme overrides.
     */
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            // Optional Reset action for theme overrides
            Action::make('resetOverrides')
                ->label('Reset to Preset')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record?->key === 'appearance.theme_overrides')
                ->action(function () {
                    app(ThemeService::class)->resetToPreset();

                    Notification::make()
                        ->title('Theme overrides have been reset to preset defaults.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
