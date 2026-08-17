<?php

/*
Component: Settings Seeder (Preset-Driven)
File Path: database/seeders/SettingsSeeder.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Seeds initial configuration values into the settings table.
Provides defaults for university identity, branding, portal, and admissions.
Ensures ThemeService and SettingResource have meaningful values on first run.

Status: ✅ Production Ready
Version: 3.1 (preset + mode aware, JSON overrides, hero media support)
*/

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // University Identity
            ['key' => 'institution.name',        'value' => 'Abdulfattah Durojaiye University', 'type' => 'string',  'group' => 'institution', 'is_public' => true,  'is_editable' => true, 'sort_order' => 1],
            ['key' => 'institution.short_name',  'value' => 'ADUA',                             'type' => 'string',  'group' => 'institution', 'is_public' => true,  'is_editable' => true, 'sort_order' => 2],
            ['key' => 'institution.logo',        'value' => null,                               'type' => 'string',  'group' => 'institution', 'is_public' => true,  'is_editable' => true, 'sort_order' => 3],
            ['key' => 'institution.favicon',     'value' => null,                               'type' => 'string',  'group' => 'institution', 'is_public' => true,  'is_editable' => true, 'sort_order' => 4],
            ['key' => 'institution.website',     'value' => 'https://www.adua.edu.ng',          'type' => 'string',  'group' => 'institution', 'is_public' => true,  'is_editable' => true, 'sort_order' => 5],
            ['key' => 'institution.email',       'value' => 'admissions@adua.edu.ng',           'type' => 'string',  'group' => 'institution', 'is_public' => true,  'is_editable' => true, 'sort_order' => 6],
            ['key' => 'institution.phone',       'value' => '+234-800-000-0000',                'type' => 'string',  'group' => 'institution', 'is_public' => true,  'is_editable' => true, 'sort_order' => 7],

            // Hero Media (JSON array of image/video items)
            ['key' => 'institution.hero_media',  'value' => '[]',                               'type' => 'json',    'group' => 'institution', 'is_public' => true,  'is_editable' => true, 'sort_order' => 8],

            // Appearance (Preset + Mode + Font)
            ['key' => 'appearance.theme_preset',    'value' => 'adua-heritage',                  'type' => 'string',  'group' => 'appearance',  'is_public' => true,  'is_editable' => true, 'sort_order' => 0],
            ['key' => 'appearance.theme_mode',      'value' => 'light',                          'type' => 'string',  'group' => 'appearance',  'is_public' => true,  'is_editable' => true, 'sort_order' => 0],
            ['key' => 'appearance.font_family',     'value' => 'Inter, system-ui, sans-serif',   'type' => 'string',  'group' => 'appearance',  'is_public' => true,  'is_editable' => true, 'sort_order' => 0],

            // Theme Overrides (JSON, starts empty)
            ['key' => 'appearance.theme_overrides', 'value' => '{}',                             'type' => 'json',    'group' => 'appearance',  'is_public' => true,  'is_editable' => true, 'sort_order' => 1],

            // Portal
            ['key' => 'portal.application_title',   'value' => 'Undergraduate Admissions 2026',  'type' => 'string',  'group' => 'portal',      'is_public' => true,  'is_editable' => true, 'sort_order' => 1],
            ['key' => 'portal.welcome_message',     'value' => 'Welcome to Abdulfattah Durojaiye University Admissions Portal.', 'type' => 'string', 'group' => 'portal', 'is_public' => true, 'is_editable' => true, 'sort_order' => 2],
            ['key' => 'portal.footer_text',         'value' => '© 2026 Abdulfattah Durojaiye University. All rights reserved.', 'type' => 'string', 'group' => 'portal', 'is_public' => true, 'is_editable' => true, 'sort_order' => 3],
            ['key' => 'portal.enable_applications', 'value' => 'true',                           'type' => 'boolean', 'group' => 'portal',      'is_public' => false, 'is_editable' => true, 'sort_order' => 4],
            ['key' => 'portal.enable_payments',     'value' => 'true',                           'type' => 'boolean', 'group' => 'portal',      'is_public' => false, 'is_editable' => true, 'sort_order' => 5],

            // Admissions
            ['key' => 'admissions.application_start',    'value' => '2026-09-01',                'type' => 'date',    'group' => 'admissions',  'is_public' => true,  'is_editable' => true, 'sort_order' => 1],
            ['key' => 'admissions.application_deadline', 'value' => '2026-11-30',                'type' => 'date',    'group' => 'admissions',  'is_public' => true,  'is_editable' => true, 'sort_order' => 2],
            ['key' => 'admissions.application_fee',      'value' => '20000',                     'type' => 'integer', 'group' => 'admissions',  'is_public' => false, 'is_editable' => true, 'sort_order' => 3],
        ];

        foreach ($settings as $data) {
            Setting::updateOrCreate(['key' => $data['key']], $data);
        }
    }
}
