<?php

/*
Component: Database Seeder
File Path: database/seeders/DatabaseSeeder.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Seeds the application's database with initial data.
Includes a default admin (officer) user, a test student user,
the full university academic structure (Faculties → Departments → Programmes),
global form fields with validation, programme-specific fields with validation,
document types with validation, and predefined settings.

Status: ✅ Production Ready
Version: 2.1 (role column integrated, officer/student accounts seeded)
*/

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create or update a default officer (admin) user
        User::updateOrCreate(
            ['email' => 'admin@adua.edu.ng'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'), // ⚠️ Change in production!
                'role' => 'officer',
            ]
        );

        // Create or update a default student test user
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'), // ⚠️ Change in production!
                'role' => 'student',
            ]
        );

        // Run all seeders safely
        $this->call([
            UniversityStructureSeeder::class,          // Faculties, Departments, Programmes
            GlobalFormFieldsSeeder::class,             // Global fields with validation rules
     //       ProgrammeSpecificFormFieldsSeeder::class,  // Programme-specific fields with validation rules
            DocumentTypeSeeder::class,                 // Document types with validation rules
            SettingsSeeder::class,                     // Portal identity, appearance, admissions settings
        ]);
    }
}
