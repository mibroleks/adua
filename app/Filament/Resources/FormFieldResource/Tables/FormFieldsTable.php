<?php

/*
Component: FormFieldsTable Schema
File Path: app/Filament/Resources/FormFieldResource/Tables/FormFieldsTable.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the Filament table schema for listing dynamic application form fields.
Supports label, key, type, required flag, options, active status, and timestamps.

Status: ✅ Production Ready
Version: 1.1 (Filament v3 compatible)
*/

namespace App\Filament\Resources\FormFieldResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class FormFieldsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Field Label')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('key')
                    ->label('Field Key')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Field Type')
                    ->sortable(),

                Tables\Columns\IconColumn::make('required')
                    ->label('Required')
                    ->boolean(),

                Tables\Columns\TextColumn::make('options')
                    ->label('Options')
                    ->toggleable(isToggledHiddenByDefault: true),

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
