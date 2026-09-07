<?php

/*
Component: Document Type Seeder
File Path: database/seeders/DocumentTypeSeeder.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Seeds the database with required application document types.
Includes only global requirements (apply to all programmes).
Uses updateOrCreate to avoid duplicate key errors.
*/

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApplicationDocumentType;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        // 🌍 Global Document Types
        ApplicationDocumentType::updateOrCreate(
            ['programme_id' => null, 'key' => 'birth_certificate'],
            [
                'name'               => 'Birth Certificate',
                'required'           => true,
                'allowed_file_types' => ['pdf','jpg','png'],
                'max_size'           => 5120, // KB
                'active'             => true,
            ]
        );

        ApplicationDocumentType::updateOrCreate(
            ['programme_id' => null, 'key' => 'olevel_result'],
            [
                'name'               => 'O’Level Result',
                'required'           => true,
                'allowed_file_types' => ['pdf','jpg','png'],
                'max_size'           => 5120,
                'active'             => true,
            ]
        );

        ApplicationDocumentType::updateOrCreate(
            ['programme_id' => null, 'key' => 'passport_photo'],
            [
                'name'               => 'Passport Photograph',
                'required'           => true,
                'allowed_file_types' => ['jpg','png'],
                'max_size'           => 2048,
                'active'             => true,
            ]
        );

        ApplicationDocumentType::updateOrCreate(
            ['programme_id' => null, 'key' => 'national_id'],
            [
                'name'               => 'National ID / International Passport',
                'required'           => true,
                'allowed_file_types' => ['pdf','jpg','png'],
                'max_size'           => 5120,
                'active'             => true,
            ]
        );
    }
}
