<?php

/*
Component: Programmes Table
File Path: app/Filament/Resources/ProgrammeResource/Tables/ProgrammesTable.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the table view for programmes in Filament.
Shows key programme details and provides record/bulk actions.

Status: ✅ Production Ready
Version: 1.4 (Filament v3 compatible, synced with migration/model)
*/

namespace App\Filament\Resources\ProgrammeResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class ProgrammesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Programme Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('degree_type')
                    ->label('Degree Type'),

                Tables\Columns\TextColumn::make('duration')
                    ->label('Duration (years)'),

                Tables\Columns\TextColumn::make('application_fee')
                    ->label('Application Fee (₦)')
                    ->money('NGN', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('tuition')
                    ->label('Tuition (₦)')
                    ->money('NGN', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('credits')
                    ->label('Credits')
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivery_mode')
                    ->label('Delivery Mode'),

                Tables\Columns\TextColumn::make('language')
                    ->label('Language'),

                Tables\Columns\TextColumn::make('requirements')
                    ->label('Requirements')
                    ->limit(30),

                Tables\Columns\TextColumn::make('career_paths')
                    ->label('Career Paths')
                    ->limit(30),

                Tables\Columns\TextColumn::make('scholarships')
                    ->label('Scholarships')
                    ->limit(30),

                Tables\Columns\TextColumn::make('accreditation_body')
                    ->label('Accreditation'),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('department.faculty.name')
                    ->label('Faculty')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\IconColumn::make('application_enabled')
                    ->label('Admissions Enabled')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Active Status')
                    ->boolean(),

                Tables\Filters\TernaryFilter::make('application_enabled')
                    ->label('Admissions Enabled')
                    ->boolean(),
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
