<?php

/*
Component: ApplicationDocumentsTable
File Path: app/Filament/Resources/ApplicationDocumentResource/Tables/ApplicationDocumentsTable.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the table schema for listing application documents in Filament.
Shows applicant, programme, document type, status, upload details, and a direct download link.
Supports filters and bulk actions for officer review.

Status: ✅ Production Ready
Version: 1.2
*/

namespace App\Filament\Resources\ApplicationDocumentResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Storage;

class ApplicationDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('application.application_number')
                    ->label('Application No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('application.user.name')
                    ->label('Applicant')
                    ->searchable(),

                TextColumn::make('application.programme.name')
                    ->label('Programme')
                    ->sortable(),

                TextColumn::make('type.name')
                    ->label('Document Type')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->colors([
                        'primary' => 'PENDING',
                        'success' => 'VERIFIED',
                        'danger'  => 'REJECTED',
                    ])
                    ->sortable(),

                TextColumn::make('uploaded_at')
                    ->label('Uploaded At')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                // Direct download link
                TextColumn::make('download')
                    ->label('Download')
                    ->url(fn ($record) => $record->path 
                        ? Storage::disk($record->disk)->url($record->path) 
                        : null
                    )
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->path))
                    ->formatStateUsing(fn () => 'Open File'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'PENDING'  => 'Pending',
                        'VERIFIED' => 'Verified',
                        'REJECTED' => 'Rejected',
                    ]),
                SelectFilter::make('application.programme_id')
                    ->relationship('application.programme', 'name')
                    ->label('Programme'),
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
