<?php

/*
Component: FormFieldInfolist Schema
File Path: app/Filament/Resources/FormFieldResource/Schemas/FormFieldInfolist.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the Filament infolist schema for displaying details of a dynamic application form field.
Supports label, key, type, required flag, options, and active status.

Status: ✅ Production Ready
Version: 1.1 (Filament v3 compatible)
*/

namespace App\Filament\Resources\FormFieldResource\Schemas;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;

class FormFieldInfolist
{
    public static function configure(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('label')
                ->label('Field Label'),

            TextEntry::make('key')
                ->label('Field Key'),

            TextEntry::make('type')
                ->label('Field Type'),

            IconEntry::make('required')
                ->boolean()
                ->label('Required'),

            TextEntry::make('options')
                ->label('Options'),

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
