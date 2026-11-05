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
| Provides centralized access to institutional branding and theme tokens.
| Used by the student portal (CSS variables) and officer panel.
|
| Architecture:
| - Settings table stores institutional identity + appearance values.
| - ThemeService exposes typed getters for brand + component tokens.
| - Supports presets and light/dark modes.
| - Supports individual student portal theme preferences.
| - Personal preferences never modify institutional settings.
| - Overrides are stored as JSON in appearance.theme_overrides.
|
| Status: Production Ready
| Version: 5.4 (personal portal theme preference support)
|--------------------------------------------------------------------------
*/

namespace App\Services;

use App\Models\Setting;
use App\Models\User;

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
    | Preset + Institutional Mode
    |--------------------------------------------------------------------------
    */

    public function preset(): string
    {
        return Setting::get(
            'appearance.theme_preset',
            config('theme.default')
        );
    }

    /**
     * Return the institution's configured theme mode.
     *
     * This remains independent of personal user preferences.
     */
    public function mode(): string
    {
        $mode = Setting::get('appearance.theme_mode', 'light');

        if (! in_array($mode, ['light', 'dark'], true)) {
            return 'light';
        }

        return $mode;
    }

    /**
     * Resolve the effective mode for the authenticated portal user.
     *
     * Students may override the institutional default.
     * Officers and guests retain the institutional mode.
     */
    public function portalMode(?User $user = null): string
    {
        if ($user && $user->isStudent()) {
            return $user->preferredThemeMode() ?? $this->mode();
        }

        return $this->mode();
    }

    /*
    |--------------------------------------------------------------------------
    | Tokens
    |--------------------------------------------------------------------------
    */

    /**
     * Resolve theme tokens for a specific mode.
     *
     * When no mode is supplied, use the institutional mode.
     */
    public function tokens(?string $mode = null): array
    {
        $presetKey = $this->preset();

        $mode = in_array($mode, ['light', 'dark'], true)
            ? $mode
            : $this->mode();

        $defaultPreset = config('theme.default');

        $preset = config(
            "theme.presets.{$presetKey}",
            config("theme.presets.{$defaultPreset}")
        );

        $tokens = $preset[$mode] ?? $preset['light'] ?? [];

        return $this->applyOverrides($tokens);
    }

    /**
     * Apply configured institutional colour overrides.
     */
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
            if (
                array_key_exists($key, $tokens)
                && $this->isValidColor($value)
            ) {
                $tokens[$key] = $value;
            }
        }

        return $tokens;
    }

    private function isValidColor(?string $value): bool
    {
        return $value !== null
            && preg_match('/^#[0-9A-Fa-f]{6}$/', $value) === 1;
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
    | Reset Overrides
    |--------------------------------------------------------------------------
    */

    public function resetToPreset(): void
    {
        Setting::updateOrCreate(
            ['key' => 'appearance.theme_overrides'],
            [
                'value' => '{}',
                'type' => 'json',
                'group' => 'appearance',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'appearance.overrides_enabled'],
            [
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'appearance',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Export
    |--------------------------------------------------------------------------
    */

    public function toCssVariables(?string $mode = null): string
    {
        $tokens = $this->tokens($mode);

        $css = ":root {\n";

        foreach ($tokens as $key => $value) {
            $normalizedKey = str_replace('_', '-', $key);

            $css .= "    --theme-{$normalizedKey}-value: {$value};\n";
            $css .= "    --theme-{$normalizedKey}: var(--theme-{$normalizedKey}-value);\n";
        }

        $css .= "    --theme-font-family-value: {$this->fontFamily()};\n";
        $css .= "    --theme-font-family: var(--theme-font-family-value);\n";

        $css .= "}";

        return $css;
    }

    public function toArray(?string $mode = null): array
    {
        $effectiveMode = in_array($mode, ['light', 'dark'], true)
            ? $mode
            : $this->mode();

        $tokens = $this->tokens($effectiveMode);

        return array_merge($tokens, [
            'preset' => $this->preset(),
            'mode' => $effectiveMode,
            'font' => $this->fontFamily(),
            'logo' => $this->logoUrl(),
            'favicon' => $this->faviconUrl(),
        ]);
    }
}