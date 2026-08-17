<?php

/*
|--------------------------------------------------------------------------
| Theme Registry
|--------------------------------------------------------------------------
| File: config/theme.php
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Central registry for premium institutional visual identities.
|
| Architecture:
| - A PRESET defines the visual personality.
| - A MODE defines luminance behavior.
| - Overrides are optional and applied by ThemeService.
| - The database stores only the selected preset/mode/overrides.
|
| Important:
| Presets are NOT merely color palettes.
| They define a complete visual atmosphere:
| brand, canvas, typography, borders, interactive, semantic, atmosphere, glass,
| plus component-level tokens (footer, card, button, modal, countdown).
|--------------------------------------------------------------------------
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Default Preset
    |--------------------------------------------------------------------------
    */
    'default' => 'adua-heritage',

    /*
    |--------------------------------------------------------------------------
    | Supported Modes
    |--------------------------------------------------------------------------
    */
    'modes' => [
        'light',
        'dark',
    ],

    /*
    |--------------------------------------------------------------------------
    | Presets
    |--------------------------------------------------------------------------
    | Each preset is defined in its own file under config/presets.
    | This keeps the registry clean and allows component-level expansion.
    */
    'presets' => [
        'adua-heritage'   => require __DIR__.'/presets/adua-heritage.php',
        'adua-forest'     => require __DIR__.'/presets/adua-forest.php',
        'adua-sapphire'   => require __DIR__.'/presets/adua-sapphire.php',
        'adua-atlantic'   => require __DIR__.'/presets/adua-atlantic.php',
        'adua-royal'      => require __DIR__.'/presets/adua-royal.php',
        'adua-terracotta' => require __DIR__.'/presets/adua-terracotta.php',
        'adua-obsidian'   => require __DIR__.'/presets/adua-obsidian.php',
    ],
];
