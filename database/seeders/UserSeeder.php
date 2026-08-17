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
        // Default admin user
        User::updateOrCreate(
            ['email' => 'admin@adua.edu.ng'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'), // change in production!
            ]
        );

        // Default test user
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'), // change in production!
            ]
        );

        // Optionally seed multiple demo users via factory
        User::factory()->count(5)->create();
    }
}
