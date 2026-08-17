<?php

/*
|--------------------------------------------------------------------------
| Component: Theme Service (Preset-Driven)
|--------------------------------------------------------------------------
| File Path: app/Services/ThemeService.php
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Provides centralized access to university branding and theme tokens.
| Used by both the student portal (CSS variables) and officer panel (Filament theme).
| Reads values from the settings table via the Setting model and resolves presets from config/theme.php.
|
| Architecture:
| - Settings table stores identity + appearance values.
| - ThemeService exposes typed getters for brand + component tokens.
| - Supports presets (Heritage, Forest, Sapphire, Atlantic, Royal, Terracotta, Obsidian) and modes (light/dark/system).
| - Prevents hardcoding of institutional identity and colors.
| - Allows optional overrides for advanced customization.
| - Overrides are stored as JSON in `appearance.theme_overrides`.
| - Officers can disable overrides via `overrides_enabled`.
|
| Status: ✅ Production Ready
| Version: 5.3 (safe defaults via hardened Setting::get, preset + mode aware, JSON overrides respected)
|--------------------------------------------------------------------------
*/

namespace App\Services;

use App\Models\Setting;

class ThemeService
{
    /*
    |--------------------------------------------------------------------------
    | Identity
    |--------------------------------------------------------------------------
    */
    public function institutionName(): string
    {
        return Setting::get('institution.name', 'University');
    }

    public function shortName(): string
    {
        return Setting::get('institution.short_name', 'UNIV');
    }

    public function logo(): ?string
    {
        return Setting::get('institution.logo', null);
    }

    public function favicon(): ?string
    {
        return Setting::get('institution.favicon', null);
    }

    public function logoUrl(): ?string
    {
        $path = $this->logo();
        return $path ? asset('storage/' . $path) : null;
    }

    public function faviconUrl(): ?string
    {
        $path = $this->favicon();
        return $path ? asset('storage/' . $path) : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Preset + Mode
    |--------------------------------------------------------------------------
    */
    public function preset(): string
    {
        return Setting::get('appearance.theme_preset', config('theme.default'));
    }

    public function mode(): string
    {
        $mode = Setting::get('appearance.theme_mode', 'light');

        // Normalize "system" → default to light for now
        if ($mode === 'system') {
            $mode = 'light';
        }

        return $mode;
    }

    /*
    |--------------------------------------------------------------------------
    | Tokens
    |--------------------------------------------------------------------------
    */
    public function tokens(): array
    {
        $presetKey = $this->preset();
        $mode = $this->mode();

        $preset = config("theme.presets.{$presetKey}", config('theme.presets.' . config('theme.default')));
        $tokens = $preset[$mode] ?? $preset['light'];

        // Apply overrides if enabled
        return $this->applyOverrides($tokens);
    }

    private function applyOverrides(array $tokens): array
    {
        $enabled = Setting::get('appearance.overrides_enabled', true);
        if (! $enabled) {
            return $tokens;
        }

        $overrides = Setting::get('appearance.theme_overrides', []);

        if (is_string($overrides)) {
            $overrides = json_decode($overrides, true) ?? [];
        }

        if (! is_array($overrides)) {
            $overrides = [];
        }

        foreach ($overrides as $key => $value) {
            if (array_key_exists($key, $tokens) && $this->isValidColor($value)) {
                $tokens[$key] = $value;
            }
        }

        return $tokens;
    }

    private function isValidColor(?string $value): bool
    {
        return $value && preg_match('/^#[0-9A-Fa-f]{6}$/', $value);
    }

    public function fontFamily(): string
    {
        return Setting::get(
            'appearance.font_family',
            'Inter, system-ui, sans-serif'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reset overrides
    |--------------------------------------------------------------------------
    */
    public function resetToPreset(): void
    {
        Setting::updateOrCreate(
            ['key' => 'appearance.theme_overrides'],
            ['value' => '{}', 'type' => 'json', 'group' => 'appearance']
        );

        Setting::updateOrCreate(
            ['key' => 'appearance.overrides_enabled'],
            ['value' => 'false', 'type' => 'boolean', 'group' => 'appearance']
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Export
    |--------------------------------------------------------------------------
    */
    public function toCssVariables(): string
    {
        $tokens = $this->tokens();

        $css = ":root {\n";
        foreach ($tokens as $key => $value) {
            // Normalize underscores to hyphens for CSS variable names
            $normalizedKey = str_replace('_', '-', $key);

            // Raw value variable
            $css .= "    --theme-{$normalizedKey}-value: {$value};\n";

            // Canonical semantic variable pointing to raw value
            $css .= "    --theme-{$normalizedKey}: var(--theme-{$normalizedKey}-value);\n";
        }

        // Font family
        $css .= "    --theme-font-family-value: {$this->fontFamily()};\n";
        $css .= "    --theme-font-family: var(--theme-font-family-value);\n";

        $css .= "}";

        return $css;
    }

    public function toArray(): array
    {
        $tokens = $this->tokens();

        return array_merge($tokens, [
            'preset'    => $this->preset(),
            'mode'      => $this->mode(),
            'font'      => $this->fontFamily(),
            'logo'      => $this->logoUrl(),
            'favicon'   => $this->faviconUrl(),
        ]);
    }
}
