<?php

/*
Component: ApplicationDocumentTypeInfolist Schema
File Path: app/Filament/Resources/ApplicationDocumentTypeResource/Schemas/ApplicationDocumentTypeInfolist.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the Filament infolist schema for displaying details of an application document type.
Supports name, key, required flag, allowed file types, max size, and active status.

Status: ✅ Production Ready
Version: 1.1 (Filament v3 compatible)
*/

namespace App\Filament\Resources\ApplicationDocumentTypeResource\Schemas;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;

class ApplicationDocumentTypeInfolist
{
    public static function configure(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('name')
                ->label('Document Name'),

            TextEntry::make('key')
                ->label('Key'),

            IconEntry::make('required')
                ->boolean()
                ->label('Required'),

            TextEntry::make('allowed_file_types')
                ->label('Allowed File Types'),

            TextEntry::make('max_size')
                ->label('Max Size (KB)'),

            IconEntry::make('active')
                ->boolean()
                ->label('Active'),

            TextEntry::make('created_at')
                ->dateTime()
                ->label('Created At'),

            TextEntry::make('updated_at')
                ->dateTime()
                ->label('Last Updated'),
        ]);
    }
}
