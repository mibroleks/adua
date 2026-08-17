<?php

/*
Component: CreateApplicationDocument Page
File Path: app/Filament/Resources/ApplicationDocumentResource/Pages/CreateApplicationDocument.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides the create page for application documents in Filament.
Allows officers to upload and register new application documents.

Status: ✅ Production Ready
Version: 1.3
*/

namespace App\Filament\Resources\ApplicationDocumentResource\Pages;

use App\Filament\Resources\ApplicationDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateApplicationDocument extends CreateRecord
{
    protected static string $resource = ApplicationDocumentResource::class;

    /**
     * Customize the page title.
     * Must be public to match parent class signature.
     */
    public function getTitle(): string
    {
        return 'Upload Application Document';
    }

    /**
     * Override success redirect to return to index.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
