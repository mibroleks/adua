<?php

/*
Component: Setting Resource (Preset-Driven + Hardened Fix)
File Path: app/Filament/Resources/SettingResource.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the Filament administration interface for predefined
university portal settings.

Architecture:
- Settings are predefined in the database.
- Officers may view and edit settings.
- Officers cannot create arbitrary settings.
- Officers cannot delete settings.
- Settings control university identity, appearance, portal behaviour,
  and admissions configuration.
- The same settings layer drives both the Officer Panel and Student Portal.

Status: ✅ Hardened Foundation
Version: 6.1 (conditional Hero Media schema fix)
*/

namespace App\Filament\Resources;

use App\Models\Setting;
use BackedEnum;
use UnitEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;
    protected static ?string $recordTitleAttribute = 'key';

    public static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    public static UnitEnum|string|null $navigationGroup = 'Configuration';
    public static ?string $navigationLabel = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('University Identity')
                ->description('Basic institutional identity used throughout the portal.')
                ->schema([
                    Forms\Components\TextInput::make('value')
                        ->label('Institution Name')
                        ->required()
                        ->visible(fn ($record) => $record?->key === 'institution.name'),
                    Forms\Components\TextInput::make('value')
                        ->label('Short Name')
                        ->visible(fn ($record) => $record?->key === 'institution.short_name'),
                    Forms\Components\FileUpload::make('value')
                        ->label('University Logo')
                        ->image()
                        ->directory('branding')
                        ->disk('public')
                        ->visibility('public')
                        ->visible(fn ($record) => $record?->key === 'institution.logo'),
                    Forms\Components\FileUpload::make('value')
                        ->label('Favicon')
                        ->image()
                        ->directory('branding')
                        ->disk('public')
                        ->visibility('public')
                        ->visible(fn ($record) => $record?->key === 'institution.favicon'),
                    Forms\Components\TextInput::make('value')
                        ->label('Website')
                        ->url()
                        ->visible(fn ($record) => $record?->key === 'institution.website'),
                    Forms\Components\TextInput::make('value')
                        ->label('Official Email')
                        ->email()
                        ->visible(fn ($record) => $record?->key === 'institution.email'),
                    Forms\Components\TextInput::make('value')
                        ->label('Phone')
                        ->tel()
                        ->visible(fn ($record) => $record?->key === 'institution.phone'),
                ])
                ->columns(2),

            Section::make('University Appearance')
                ->description('Controls the visual identity used by the Officer Panel and Student Portal.')
                ->schema([
                    Forms\Components\Select::make('value')
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
                        ->visible(fn ($record) => $record?->key === 'appearance.theme_preset'),

                    Forms\Components\Select::make('value')
                        ->label('Theme Mode')
                        ->options([
                            'light'  => 'Light',
                            'dark'   => 'Dark',
                            'system' => 'System',
                        ])
                        ->visible(fn ($record) => $record?->key === 'appearance.theme_mode'),

                    Forms\Components\TextInput::make('value')
                        ->label('Font Family')
                        ->visible(fn ($record) => $record?->key === 'appearance.font_family'),

                    Forms\Components\KeyValue::make('value')
                        ->label('Theme Overrides')
                        ->visible(fn ($record) => $record?->key === 'appearance.theme_overrides')
                        ->keyLabel('Token')
                        ->valueLabel('Hex Color')
                        ->addActionLabel('Add Override')
                        ->deleteActionLabel('Remove Override')
                        ->helperText('Keys: primary, secondary, accent, page, surface, body, heading, muted, inverse, border, border_strong, action, action_hover, focus, success, warning, danger, info, hero_start, hero_end, gradient_start, gradient_end, glow, glass, glass_dark, glass_border.')
                        ->rules(['regex:/^#[0-9A-Fa-f]{6}$/']),
                ])
                ->columns(2),

            Section::make('Student Portal')
                ->description('Controls the public-facing admissions and student portal experience.')
                ->schema([
                    Forms\Components\TextInput::make('value')
                        ->label('Application Title')
                        ->visible(fn ($record) => $record?->key === 'portal.application_title'),
                    Forms\Components\Textarea::make('value')
                        ->label('Welcome Message')
                        ->rows(4)
                        ->visible(fn ($record) => $record?->key === 'portal.welcome_message'),
                    Forms\Components\Textarea::make('value')
                        ->label('Footer Text')
                        ->rows(3)
                        ->visible(fn ($record) => $record?->key === 'portal.footer_text'),
                    Forms\Components\Toggle::make('value')
                        ->label('Enable Applications')
                        ->visible(fn ($record) => $record?->key === 'portal.enable_applications'),
                    Forms\Components\Toggle::make('value')
                        ->label('Enable Payments')
                        ->visible(fn ($record) => $record?->key === 'portal.enable_payments'),
                ])
                ->columns(2),

            Section::make('Admissions')
                ->description('Controls the current admission cycle.')
                ->schema([
                    Forms\Components\DatePicker::make('value')
                        ->label('Application Start')
                        ->visible(fn ($record) => $record?->key === 'admissions.application_start'),
                    Forms\Components\DatePicker::make('value')
                        ->label('Application Deadline')
                        ->visible(fn ($record) => $record?->key === 'admissions.application_deadline'),
                    Forms\Components\TextInput::make('value')
                        ->label('Application Fee')
                        ->numeric()
                        ->prefix('₦')
                        ->visible(fn ($record) => $record?->key === 'admissions.application_fee'),
                ])
                ->columns(2),

            Section::make('Hero Media')
                ->description('Controls the images and videos shown in the admissions hero carousel.')
                ->schema(fn ($record) => $record?->key === 'institution.hero_media' ? [
                    Forms\Components\Repeater::make('value')
                        ->label('Hero Media Items')
                        ->schema([
                            Forms\Components\FileUpload::make('url')
                                ->label('Media File')
                                ->disk('public')
                                ->visibility('public')
                                ->directory(fn ($get) => $get('type') === 'video' ? 'hero/videos' : 'hero/images')
                                ->acceptedFileTypes(['image/*', 'video/*'])
                                ->maxSize(51200)
                                ->helperText('Upload image or video file'),
                            Forms\Components\Select::make('type')
                                ->label('Type')
                                ->options([
                                    'image' => 'Image',
                                    'video' => 'Video',
                                ])
                                ->required(),
                        ])
                        ->default(fn ($record) =>
                            is_array($record?->value)
                                ? $record->value
                                : (is_string($record?->value) ? json_decode($record->value, true) ?? [] : [])
                        )
                        ->addActionLabel('Add Media')
                  //      ->deleteActionLabel('Remove Media')
                        ->columns(2),
                ] : [])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group')
                    ->label('Group')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('key')
                    ->label('Setting')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Current Value')
                    ->limit(50),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([])
            ->bulkActions([]);
    }

    /*
    | Pages
    */
    public static function getPages(): array
    {
        return [
            'index' => SettingResource\Pages\ListSettings::route('/'),
            'view'  => SettingResource\Pages\ViewSetting::route('/{record}'),
            'edit'  => SettingResource\Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}

