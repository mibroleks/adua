<?php

/*
Component: Setting Form Schema (Preset-Driven)
File Path: app/Filament/Resources/SettingResource/Schemas/SettingForm.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the Filament form schema for managing settings in the admin panel.
Ensures officers can edit values safely without creating or deleting arbitrary keys.

Architecture:
- Officers edit only predefined settings.
- Preset + mode are separate.
- Overrides are stored as JSON (appearance.theme_overrides).
- Provides reset option to clear overrides back to preset defaults.
- Adds toggle to enable/disable overrides for clearer UX.
- Adds repeater for hero media (images/videos) stored as JSON.

Status: ✅ Production Ready
Version: 3.7 (Filament v5 compatible, hero media repeater added)
*/

namespace App\Filament\Resources\SettingResource\Schemas;

use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;

class SettingForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('key')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('Key')
                    ->disabled(), // ✅ officers cannot change keys

                /*
                | Appearance Preset + Mode
                */
                Select::make('value')
                    ->label('Theme Preset')
                    ->options([
                        'adua-heritage'   => 'ADUA Heritage',
                        'adua-forest'     => 'ADUA Forest',
                        'adua-sapphire'   => 'ADUA Sapphire',
                        'adua-atlantic'   => 'ADUA Atlantic',
                        'adua-royal'      => 'ADUA Royal',
                        'adua-terracotta' => 'ADUA Terracotta',
                        'adua-obsidian'   => 'ADUA Obsidian',
                    ])
                    ->visible(fn ($record) => $record && $record->key === 'appearance.theme_preset'),

                Select::make('value')
                    ->label('Theme Mode')
                    ->options([
                        'light'  => 'Light',
                        'dark'   => 'Dark',
                        'system' => 'System',
                    ])
                    ->visible(fn ($record) => $record && $record->key === 'appearance.theme_mode'),

                TextInput::make('value')
                    ->label('Font Family')
                    ->visible(fn ($record) => $record && $record->key === 'appearance.font_family'),

                /*
                | Branding Assets
                */
                FileUpload::make('value')
                    ->label('University Logo')
                    ->image()
                    ->directory('branding')
                    ->disk('public')        // ✅ force public disk
                    ->visibility('public')  // ✅ ensure public visibility
                    ->visible(fn ($record) => $record && $record->key === 'institution.logo'),

                FileUpload::make('value')
                    ->label('Favicon')
                    ->image()
                    ->directory('branding')
                    ->disk('public')        // ✅ force public disk
                    ->visibility('public')  // ✅ ensure public visibility
                    ->visible(fn ($record) => $record && $record->key === 'institution.favicon'),

                /*
                | Hero Media (images/videos)
                */
                Section::make('Hero Media')
                    ->description('Manage images and videos for the admissions hero carousel.')
                    ->schema([
                        Repeater::make('value')
                            ->label('Hero Media Items')
                            ->visible(fn ($record) => $record && $record->key === 'institution.hero_media')
                            ->schema([
                                FileUpload::make('url')
                                    ->label('Media File')
                                    ->directory('hero')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->helperText('Upload image or video file'),
                                Select::make('type')
                                    ->label('Type')
                                    ->options([
                                        'image' => 'Image',
                                        'video' => 'Video',
                                    ])
                                    ->required(),
                            ])
                            ->addButtonLabel('Add Media')
                            ->deleteButtonLabel('Remove Media')
                            ->columns(2),
                    ]),

                /*
                | Theme Overrides
                */
                Section::make('Theme Overrides')
                    ->description('Customize preset tokens. Disable to use preset defaults.')
                    ->schema([
                        Toggle::make('value')
                            ->label('Enable Customization')
                            ->visible(fn ($record) => $record && $record->key === 'appearance.overrides_enabled')
                            ->helperText('Turn off to use preset defaults only.'),

                        KeyValue::make('value')
                            ->keyLabel('Token')
                            ->valueLabel('Hex Color')
                            ->addButtonLabel('Add Override')
                            ->deleteButtonLabel('Remove')
                            ->helperText('Keys: primary, secondary, accent, page, surface, body, heading, muted, inverse, border, border_strong, action, action_hover, focus, success, warning, danger, info, hero_start, hero_end, gradient_start, gradient_end, glow, glass, glass_dark, glass_border.')
                            ->rules(['regex:/^#[0-9A-Fa-f]{6}$/']) // ✅ validate hex colors
                            ->visible(fn ($record) => $record && $record->key === 'appearance.theme_overrides'),
                    ]),

                /*
                | Fallback for non-appearance values
                */
                TextInput::make('value')
                    ->label('Value')
                    ->visible(fn ($record) => $record && $record->group !== 'appearance'),

                Select::make('type')
                    ->options([
                        'string'  => 'String',
                        'integer' => 'Integer',
                        'boolean' => 'Boolean',
                        'date'    => 'Date',
                        'json'    => 'JSON',
                    ])
                    ->default('string')
                    ->label('Type')
                    ->disabled(),

                TextInput::make('group')
                    ->label('Group')
                    ->disabled(),
            ]);
    }
}
