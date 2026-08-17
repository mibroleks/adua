<?php

/*
Component: ApplicationDocument Exporter
File Path: app/Filament/Exports/ApplicationDocumentExporter.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the exporter for ApplicationDocument records in Filament.
Supports exporting selected fields to CSV, XLSX, or other formats.
Implements notification body after export completion.

Status: ✅ Production Ready
Version: 1.4 (added type hints + clarified columns)
*/

namespace App\Filament\Exports;

use App\Models\ApplicationDocument;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class ApplicationDocumentExporter extends Exporter
{
    /**
     * The underlying model for export.
     */
    protected static ?string $model = ApplicationDocument::class;

    /**
     * Define the columns to be exported.
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('application_id')->label('Application ID'),
            ExportColumn::make('documentType.name')->label('Document Type'),
            ExportColumn::make('original_name')->label('Original File Name'),
            ExportColumn::make('mime_type')->label('MIME Type'),
            ExportColumn::make('size')->label('File Size (bytes)'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('rejection_reason')->label('Rejection Reason'),
            ExportColumn::make('uploaded_at')->label('Uploaded At'),
            ExportColumn::make('verified_at')->label('Verified At'),
            ExportColumn::make('officer.name')->label('Verified By'),
            ExportColumn::make('created_at')->label('Created At'),
            ExportColumn::make('updated_at')->label('Last Updated'),
        ];
    }

    /**
     * Notification body shown when export completes.
     */
    public static function getCompletedNotificationBody(Export $export): string
    {
        return '✅ Your export of application documents has completed successfully. You can now download the file.';
    }
}
