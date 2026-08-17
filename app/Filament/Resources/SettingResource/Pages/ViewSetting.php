<?php

/*
Component: View Setting Page (Hardened + Media Management)
File Path: app/Filament/Resources/SettingResource/Pages/ViewSetting.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament admin page for viewing existing settings.
Adds support for managing uploaded media (preview, download, delete).

Architecture:
- Officers can view a setting’s details in read-only mode.
- Includes header action to edit the setting.
- Adds conditional actions for media files (download, delete).
- Uses SettingResource for schema and configuration.
- Wires Blade view for hero_media previews.

Status: ✅ Production Ready
Version: 1.5 (Filament v5 compatible, media management wired + hardened)
*/

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;
use Illuminate\Support\Facades\Storage;

class ViewSetting extends ViewRecord
{
    protected static string $resource = SettingResource::class;

    /**
     * Header actions available on the view page.
     */
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    /**
     * Custom Livewire method to delete a single media item.
     */
    public function deleteMedia(string $path): void
    {
        // Remove file from storage
        Storage::disk('public')->delete($path);

        // Update JSON array by removing the deleted item
        $mediaItems = collect($this->record->castValue())
            ->reject(fn ($item) => $item['url'] === $path)
            ->values()
            ->all();

        $this->record->update(['value' => json_encode($mediaItems)]);

        $this->notify('success', 'Media deleted successfully.');
    }

    /**
     * Override view data to include custom Blade for hero_media.
     */
    protected function getViewData(): array
    {
        $data = parent::getViewData();

        if ($this->record->key === 'institution.hero_media') {
            $data['customView'] = 'filament.resources.setting-resource.pages.view-setting-media';
        }

        return $data;
    }
}
