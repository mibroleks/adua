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
Now also displays document history for audit trail.

Status: ✅ Production Ready
Version: 1.5 (Filament v5 compatible, fixed relationship + added history section)
*/

namespace App\Filament\Resources\ApplicationDocumentResource\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
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

            // ✅ Fixed relationship naming
            TextEntry::make('documentType.name')
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

            // ✅ Officer label clarified
            TextEntry::make('officer.name')
                ->label('Reviewed By'),

            TextEntry::make('uploaded_at')
                ->label('Uploaded At')
                ->dateTime('d M Y, H:i'),

            TextEntry::make('verified_at')
                ->label('Reviewed At')
                ->dateTime('d M Y, H:i'),

            // ✅ Show rejection reason only when REJECTED
            TextEntry::make('rejection_reason')
                ->label('Rejection Reason')
                ->visible(fn ($record) => $record->status === 'REJECTED')
                ->columnSpanFull(),

            // Direct download link
            TextEntry::make('path')
                ->label('Download Document')
                ->url(fn ($record) => $record->path 
                    ? Storage::disk($record->disk)->url($record->path) 
                    : null
                )
                ->openUrlInNewTab()
                ->visible(fn ($record) => !empty($record->path)),

            // ✅ Document history audit trail
            RepeatableEntry::make('histories')
                ->label('Document History')
                ->schema([
                    TextEntry::make('action')
                        ->label('Action')
                        ->badge(),

                    TextEntry::make('old_status')
                        ->label('Previous Status'),

                    TextEntry::make('new_status')
                        ->label('New Status'),

                    TextEntry::make('officer.name')
                        ->label('Performed By'),

                    TextEntry::make('remarks')
                        ->label('Remarks')
                        ->columnSpanFull(),

                    TextEntry::make('performed_at')
                        ->label('Date')
                        ->dateTime('d M Y, H:i'),
                ])
                ->columnSpanFull(),
        ]);
    }
}
