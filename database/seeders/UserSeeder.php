<?php

/*
Component: User Seeder
File Path: database/seeders/UserSeeder.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Safely seeds initial user accounts for development and controlled
production officer provisioning.

Production Safety:
- Production provisioning is disabled by default.
- Production can provision admission officers only through
  an explicit temporary opt-in.
- Default administrator and test student are never seeded
  in production.
- Existing accounts are never overwritten.
- Existing passwords, names and roles are preserved.
- New production officer accounts require individual passwords
  of at least 8 characters.

Responsibilities:
- Seed the default system administrator in development.
- Seed the optional test student in development.
- Seed admission officer accounts in development.
- Allow controlled production creation of missing officers.
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
        if (app()->environment('production')) {
            $this->seedProductionOfficers();

            return;
        }

        /*
         * Development system administrator.
         */
        $this->seedUser(
            email: 'admin@adua.edu.ng',
            name: 'System Administrator',
            role: 'officer',
            password: $this->developmentPassword('SEED_ADMIN_PASSWORD')
        );

        /*
         * Development test student.
         */
        $this->seedUser(
            email: 'test@example.com',
            name: 'Test User',
            role: 'student',
            password: $this->developmentPassword('SEED_TEST_PASSWORD')
        );

        /*
         * Development admission officer 1.
         */
        $this->seedUser(
            email: 'obilor.IT@adua.edu.ng',
            name: 'Obilor (Admission Officer)',
            role: 'officer',
            password: $this->developmentPassword('SEED_OBILOR_PASSWORD')
        );

        /*
         * Development admission officer 2.
         */
        $this->seedUser(
            email: 'dikko.IT@adua.edu.ng',
            name: 'Dikko (Admission Officer)',
            role: 'officer',
            password: $this->developmentPassword('SEED_DIKKO_PASSWORD')
        );

        /*
         * Optional development demo users.
         */
        // User::factory()->count(5)->create();
    }

    /**
     * Provision only the approved admission officers in production.
     *
     * This operation requires an explicit opt-in and individual
     * passwords supplied through the process environment.
     */
    private function seedProductionOfficers(): void
    {
        if (!$this->productionFlagEnabled('SEED_PRODUCTION_OFFICERS')) {
            $this->command?->warn(
                'UserSeeder skipped: production officer provisioning '
                . 'has not been explicitly enabled.'
            );

            return;
        }

        $this->command?->warn(
            'Production officer provisioning is enabled. '
            . 'Only the two designated officer accounts will be considered.'
        );

        $this->seedUser(
            email: 'obilor.IT@adua.edu.ng',
            name: 'Obilor (Admission Officer)',
            role: 'officer',
            password: $this->productionPassword('SEED_OBILOR_PASSWORD')
        );

        $this->seedUser(
            email: 'dikko.IT@adua.edu.ng',
            name: 'Dikko (Admission Officer)',
            role: 'officer',
            password: $this->productionPassword('SEED_DIKKO_PASSWORD')
        );
    }

    /**
     * Create an account only when it does not already exist.
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
                "Skipped {$email}: no valid seeding password supplied."
            );

            return;
        }

        if (strlen($password) < 8) {
            $this->command?->warn(
                "Skipped {$email}: password must contain at least 8 characters."
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
                ? "Created account: {$email}"
                : "Preserved existing account without changes: {$email}"
        );
    }

    /**
     * Read development passwords from the Laravel environment.
     */
    private function developmentPassword(string $key): ?string
    {
        $value = env($key);

        return is_string($value) && $value !== ''
            ? $value
            : null;
    }

    /**
     * Read production passwords only from explicitly supplied
     * process environment variables.
     *
     * This also works when Laravel configuration is cached,
     * provided the variables are supplied to the Artisan process.
     */
    private function productionPassword(string $key): ?string
    {
        $value = getenv($key);

        return is_string($value) && $value !== ''
            ? $value
            : null;
    }

    /**
     * Check an explicit production opt-in flag.
     */
    private function productionFlagEnabled(string $key): bool
    {
        $value = getenv($key);

        return is_string($value)
            && in_array(strtolower(trim($value)), ['1', 'true', 'yes'], true);
    }
}
