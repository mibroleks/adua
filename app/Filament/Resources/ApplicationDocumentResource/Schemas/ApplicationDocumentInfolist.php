<?php

/*
Component: ApplicationDocumentInfolist
File Path: app/Filament/Resources/ApplicationDocumentResource/Schemas/ApplicationDocumentInfolist.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the infolist schema for viewing application documents in Filament.
Provides read-only display of applicant, programme, file metadata, status, officer, remarks,
and a direct download link to the uploaded file.

Status: ✅ Production Ready
Version: 1.3
*/

namespace App\Filament\Resources\ApplicationDocumentResource\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\DateTimeEntry;
use Illuminate\Support\Facades\Storage;

class ApplicationDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('application.application_number')
                ->label('Application Number'),

            TextEntry::make('application.user.name')
                ->label('Applicant'),

            TextEntry::make('application.programme.name')
                ->label('Programme'),

            TextEntry::make('type.name')
                ->label('Document Type'),

            TextEntry::make('original_name')
                ->label('File Name'),

            TextEntry::make('mime_type')
                ->label('MIME Type'),

            TextEntry::make('size')
                ->label('Size (bytes)'),

            TextEntry::make('status')
                ->badge()
                ->colors([
                    'secondary' => 'PENDING',
                    'success'   => 'VERIFIED',
                    'danger'    => 'REJECTED',
                ]),

            TextEntry::make('officer.name')
                ->label('Verified By'),

            DateTimeEntry::make('uploaded_at')
                ->label('Uploaded At'),

            DateTimeEntry::make('verified_at')
                ->label('Verified At'),

            TextEntry::make('rejection_reason')
                ->label('Rejection Reason')
                ->visible(fn ($record) => $record->status === 'REJECTED'),

            // Direct download link
            TextEntry::make('path')
                ->label('Download Document')
                ->url(fn ($record) => $record->path 
                    ? Storage::disk($record->disk)->url($record->path) 
                    : null
                )
                ->openUrlInNewTab()
                ->visible(fn ($record) => !empty($record->path)),
        ]);
    }
}
