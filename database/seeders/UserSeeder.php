<?php

/*
Component: User Seeder
File Path: database/seeders/UserSeeder.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Seeds initial user accounts into the database.
Provides a default admin user and optional test accounts
for development and officer panel access.

Responsibilities:
- Seed the default system administrator.
- Seed the default test student.
- Seed admission officer accounts.
- Generate optional demo users for development.
*/

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default system administrator
        User::updateOrCreate(
            ['email' => 'admin@adua.edu.ng'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'), // Change in production!
                'role' => 'officer',
            ]
        );

        // Default test student
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'), // Change in production!
                'role' => 'student',
            ]
        );

        // Admission Officer 1
        User::updateOrCreate(
            ['email' => 'obilor.IT@adua.edu.ng'],
            [
                'name' => 'Obilor (Admission Officer)',
                'password' => Hash::make('ADUA@2027'),
                'role' => 'officer',
            ]
        );

        // Admission Officer 2
        User::updateOrCreate(
            ['email' => 'dikko.IT@adua.edu.ng'],
            [
                'name' => 'Dikko (Admission Officer)',
                'password' => Hash::make('ADUA@2027'),
                'role' => 'officer',
            ]
        );

        // Generate optional demo users for development
       // User::factory()->count(5)->create();
    }
}
