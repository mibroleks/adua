<?php

/*
Component: ApplicationDocumentTypesTable Schema
File Path: app/Filament/Resources/ApplicationDocumentTypeResource/Tables/ApplicationDocumentTypesTable.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the Filament table schema for listing application document types.
Supports name, key, required flag, allowed file types, max size, and active status.

Status: ✅ Production Ready
Version: 1.1 (Filament v3 compatible)
*/

namespace App\Filament\Resources\ApplicationDocumentTypeResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class ApplicationDocumentTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Document Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('required')
                    ->label('Required')
                    ->boolean(),

                Tables\Columns\TextColumn::make('allowed_file_types')
                    ->label('Allowed File Types'),

                Tables\Columns\TextColumn::make('max_size')
                    ->label('Max Size (KB)')
                    ->sortable(),

                Tables\Columns\IconColumn::make('active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('required')
                    ->label('Required'),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('Active'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
