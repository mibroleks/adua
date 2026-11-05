<?php

/*
Component: User Seeder
File Path: database/seeders/UserSeeder.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Safely seeds initial user accounts for development and testing.

Production Safety:
- Does not create, update, or delete accounts in production.
- Never resets existing user passwords.
- Never overwrites existing user names or roles.
- Requires explicitly configured passwords before creating new accounts.

Responsibilities:
- Seed the default system administrator in development.
- Seed the optional test student in development.
- Seed admission officer accounts in development.
- Preserve existing accounts and credentials.
*/

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
         * Production protection.
         *
         * This seeder must never provision default or demo accounts
         * on a live institutional installation.
         */
        if (app()->environment('production')) {
            $this->command?->warn(
                'UserSeeder skipped: account seeding is disabled in production.'
            );

            return;
        }

        /*
         * Development system administrator.
         */
        $this->seedUser(
            email: 'admin@adua.edu.ng',
            name: 'System Administrator',
            role: 'officer',
            password: env('SEED_ADMIN_PASSWORD')
        );

        /*
         * Development test student.
         */
        $this->seedUser(
            email: 'test@example.com',
            name: 'Test User',
            role: 'student',
            password: env('SEED_TEST_PASSWORD')
        );

        /*
         * Development admission officer 1.
         */
        $this->seedUser(
            email: 'obilor.IT@adua.edu.ng',
            name: 'Obilor (Admission Officer)',
            role: 'officer',
            password: env('SEED_OBILOR_PASSWORD')
        );

        /*
         * Development admission officer 2.
         */
        $this->seedUser(
            email: 'dikko.IT@adua.edu.ng',
            name: 'Dikko (Admission Officer)',
            role: 'officer',
            password: env('SEED_DIKKO_PASSWORD')
        );

        /*
         * Optional demo users can be generated separately
         * when required for development.
         */
        // User::factory()->count(5)->create();
    }

    /**
     * Create a development user only when the account does not exist.
     *
     * Existing accounts are never modified.
     */
    private function seedUser(
        string $email,
        string $name,
        string $role,
        ?string $password
    ): void {
        if (blank($password)) {
            $this->command?->warn(
                "Skipped {$email}: no seeding password configured."
            );

            return;
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => $role,
            ]
        );

        $this->command?->line(
            $user->wasRecentlyCreated
                ? "Created development account: {$email}"
                : "Preserved existing account: {$email}"
        );
    }
}
