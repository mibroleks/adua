<?php

/*
|--------------------------------------------------------------------------
| Component: Setting Service (Preset-Driven)
|--------------------------------------------------------------------------
| File Path: app/Services/SettingService.php
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Provides reusable methods for managing portal settings.
| Delegates branding and appearance values to ThemeService.
| Supports admission window (open/close dates) and general configuration.
|
| Status: ✅ Production Ready
| Version: 2.2 (preset + mode aware, corrected keys, hardened validation)
|--------------------------------------------------------------------------
*/

namespace App\Services;

use App\Models\Setting;
use App\Services\ThemeService;
use Illuminate\Support\Carbon;

class SettingService
{
    /**
     * Get a setting by key.
     */
    public function get(string $key, $default = null)
    {
        return Setting::where('key', $key)->value('value') ?? $default;
    }

    /**
     * Set or update a setting.
     */
    public function set(string $key, $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get institution branding (name, logo, colors).
     * Delegates appearance values to ThemeService.
     */
    public function getBranding(): array
    {
        $theme = app(ThemeService::class);
        $tokens = $theme->tokens();

        return [
            'institution_name' => $theme->institutionName(),
            'short_name'       => $theme->shortName(),
            'logo_url'         => $theme->logoUrl(),
            'favicon_url'      => $theme->faviconUrl(),
            'preset'           => $theme->preset(),
            'mode'             => $theme->mode(),
            'primary_color'    => $tokens['primary'] ?? null,
            'secondary_color'  => $tokens['secondary'] ?? null,
            'accent_color'     => $tokens['accent'] ?? null,
            'background_color' => $tokens['page'] ?? $tokens['background'] ?? null,
            'surface_color'    => $tokens['surface'] ?? null,
            'text_color'       => $tokens['body'] ?? $tokens['text'] ?? null,
        ];
    }

    /**
     * Get admission window (open/close dates).
     */
    public function getAdmissionWindow(): array
    {
        return [
            'open_date'  => $this->get('admissions.application_start'),
            'close_date' => $this->get('admissions.application_deadline'),
        ];
    }

    /**
     * Check if admissions are currently open.
     */
    public function isAdmissionOpen(): bool
    {
        $open  = $this->get('admissions.application_start');
        $close = $this->get('admissions.application_deadline');

        if (! $open || ! $close) {
            return false;
        }

        $now = Carbon::now();
        return $now->between(Carbon::parse($open), Carbon::parse($close));
    }

    /**
     * Get contact information.
     */
    public function getContactInfo(): array
    {
        return [
            'email'   => $this->get('institution.email', 'info@example.com'),
            'phone'   => $this->get('institution.phone', '+234-000-0000'),
            'address' => $this->get('institution.address', 'Campus Address'),
        ];
    }

    /**
     * Reset institution branding to defaults.
     */
    public function resetBranding(): void
    {
        $this->set('institution.name', 'University');
        $this->set('institution.short_name', 'UNIV');
        $this->set('institution.logo', null);
        $this->set('institution.favicon', null);
    }
}
