<?php

/*
|--------------------------------------------------------------------------
| Migration: Add Personal Theme Mode to Users
|--------------------------------------------------------------------------
| File:
| database/migrations/2026_11_04_000001_add_theme_mode_to_users_table.php
|
| Company: Ygrace Tech
| Author: Ibrahim Olalekan
|
| Purpose:
| Stores an authenticated user's personal portal appearance preference.
|
| Rules:
| - Light and dark are the supported personal modes.
| - Null means use the institution's configured default.
| - Does not modify institution-wide theme settings.
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('theme_mode', 10)
                ->nullable()
                ->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('theme_mode');
        });
    }
};