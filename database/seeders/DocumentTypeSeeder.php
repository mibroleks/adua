<?php

/*
Component: Document Type Seeder
File Path: database/seeders/DocumentTypeSeeder.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Seeds the database with required application document types.
Includes global requirements (apply to all programmes) and
programme-specific requirements (apply only to certain programmes).
Uses updateOrCreate to avoid duplicate key errors.
*/

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApplicationDocumentType;
use App\Models\Programme;

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

        // 🎓 Programme-Specific Examples

        // Microbiology
        $micro = Programme::where('code', 'MIC')->first();
        if ($micro) {
            ApplicationDocumentType::updateOrCreate(
                ['programme_id' => $micro->id, 'key' => 'lab_report_mic'],
                [
                    'name'               => 'Lab Report / Science Practical Certificate',
                    'required'           => false,
                    'allowed_file_types' => ['pdf','jpg','png'],
                    'max_size'           => 5120,
                    'active'             => true,
                ]
            );
        }

        // Computer Science / Cyber Security / IT
        foreach (['CSC','CYB','IFT'] as $code) {
            $prog = Programme::where('code', $code)->first();
            if ($prog) {
                ApplicationDocumentType::updateOrCreate(
                    ['programme_id' => $prog->id, 'key' => 'ict_certificate_'.$code],
                    [
                        'name'               => 'ICT Certificate',
                        'required'           => false,
                        'allowed_file_types' => ['pdf','jpg','png'],
                        'max_size'           => 5120,
                        'active'             => true,
                    ]
                );
            }
        }

        // Nursing Science
        $nursing = Programme::where('code', 'NUR')->first();
        if ($nursing) {
            ApplicationDocumentType::updateOrCreate(
                ['programme_id' => $nursing->id, 'key' => 'medical_fitness_nur'],
                [
                    'name'               => 'Medical Fitness Certificate',
                    'required'           => true,
                    'allowed_file_types' => ['pdf','jpg','png'],
                    'max_size'           => 5120,
                    'active'             => true,
                ]
            );
        }

        // Public Health
        $ph = Programme::where('code', 'PH')->first();
        if ($ph) {
            ApplicationDocumentType::updateOrCreate(
                ['programme_id' => $ph->id, 'key' => 'community_service_ph'],
                [
                    'name'               => 'Community Service Certificate',
                    'required'           => false,
                    'allowed_file_types' => ['pdf','jpg','png'],
                    'max_size'           => 5120,
                    'active'             => true,
                ]
            );
        }

        // Accounting / Finance
        foreach (['ACC','FIN'] as $code) {
            $prog = Programme::where('code', $code)->first();
            if ($prog) {
                ApplicationDocumentType::updateOrCreate(
                    ['programme_id' => $prog->id, 'key' => 'business_certificate_'.$code],
                    [
                        'name'               => 'Business Studies Certificate',
                        'required'           => false,
                        'allowed_file_types' => ['pdf','jpg','png'],
                        'max_size'           => 5120,
                        'active'             => true,
                    ]
                );
            }
        }
    }
}
