<?php

/*
Component: ApplicationDocumentForm
File Path: app/Filament/Resources/ApplicationDocumentResource/Schemas/ApplicationDocumentForm.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the form schema for managing application documents in Filament.
Allows officers to upload files, view metadata, verify/reject documents, and add remarks.
Includes validation rules so errors appear under the upload field.

Status: ✅ Production Ready
Version: 1.5 (fixed relationship reference)
*/

namespace App\Filament\Resources\ApplicationDocumentResource\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;

class ApplicationDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // Link to Application
            Select::make('application_id')
                ->relationship('application', 'application_number')
                ->label('Application')
                ->required(),

            // Link to Document Type (fixed relationship name)
            Select::make('document_type_id')
                ->relationship('documentType', 'name')
                ->label('Document Type')
                ->required(),

            // File upload (officers can attach or replace)
            FileUpload::make('path')
                ->label('Upload Document')
                ->disk('public')
                ->directory('applications/documents')
                ->preserveFilenames()
                ->openable()
                ->downloadable()
                ->required()
                // Validation rules (errors show under field)
                ->rules([
                    'file',
                    'max:5120', // 5 MB limit (adjust as needed)
                    'mimes:pdf,jpg,jpeg,png', // allowed file types
                ]),

            // File metadata (read-only)
            TextInput::make('original_name')
                ->label('File Name')
                ->disabled(),
            TextInput::make('mime_type')
                ->label('MIME Type')
                ->disabled(),
            TextInput::make('size')
                ->label('Size (bytes)')
                ->disabled(),

            // Review status
            Select::make('status')
                ->options([
                    'PENDING'  => 'Pending',
                    'VERIFIED' => 'Verified',
                    'REJECTED' => 'Rejected',
                ])
                ->required(),

            // Rejection reason (only visible if status is REJECTED)
            Textarea::make('rejection_reason')
                ->label('Rejection Reason')
                ->visible(fn ($get) => $get('status') === 'REJECTED'),

            // Officer and timestamps
            DateTimePicker::make('uploaded_at')
                ->label('Uploaded At')
                ->disabled(),
            DateTimePicker::make('verified_at')
                ->label('Verified At')
                ->default(now()),
        ]);
    }
}
