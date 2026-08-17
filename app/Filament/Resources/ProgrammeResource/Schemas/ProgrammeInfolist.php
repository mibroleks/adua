<?php

/*
Component: Programme Infolist
File Path: app/Filament/Resources/ProgrammeResource/Schemas/ProgrammeInfolist.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the read-only view page schema for programmes in Filament.
Displays key programme details in a structured infolist.

Status: ✅ Production Ready
Version: 1.3 (Filament v3 compatible, synced with migration/model)
*/

namespace App\Filament\Resources\ProgrammeResource\Schemas;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;

class ProgrammeInfolist
{
    public static function configure(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('name')
                ->label('Programme Name'),

            TextEntry::make('code')
                ->label('Programme Code'),

            TextEntry::make('description')
                ->label('Description'),

            TextEntry::make('degree_type')
                ->label('Degree Type'),

            TextEntry::make('duration')
                ->label('Duration (years)'),

            TextEntry::make('application_fee')
                ->label('Application Fee (₦)')
                ->money('NGN', true),

            TextEntry::make('tuition')
                ->label('Tuition Fee (₦)')
                ->money('NGN', true)
                ->hidden(fn ($record) => is_null($record->tuition)),

            TextEntry::make('credits')
                ->label('Total Credits')
                ->hidden(fn ($record) => is_null($record->credits)),

            TextEntry::make('delivery_mode')
                ->label('Delivery Mode')
                ->hidden(fn ($record) => empty($record->delivery_mode)),

            TextEntry::make('language')
                ->label('Language of Instruction')
                ->hidden(fn ($record) => empty($record->language)),

            TextEntry::make('requirements')
                ->label('Admission Requirements'),

            TextEntry::make('career_paths')
                ->label('Career Opportunities'),

            TextEntry::make('scholarships')
                ->label('Scholarships / Funding'),

            TextEntry::make('accreditation_body')
                ->label('Accreditation Body'),

            TextEntry::make('outcomes')
                ->label('Programme Outcomes')
                ->hidden(fn ($record) => empty($record->outcomes)),

            TextEntry::make('department.name')
                ->label('Department'),

            TextEntry::make('department.faculty.name')
                ->label('Faculty'),

            IconEntry::make('active')
                ->label('Active')
                ->boolean(),

            IconEntry::make('application_enabled')
                ->label('Admissions Enabled')
                ->boolean(),

            TextEntry::make('created_at')
                ->label('Created At')
                ->dateTime(),

            TextEntry::make('updated_at')
                ->label('Last Updated')
                ->dateTime(),
        ]);
    }
}
