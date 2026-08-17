<?php

/*
Component: Settings Table Migration (Hardened)
File Path: database/migrations/xxxx_create_settings_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Defines the settings table for dynamic configuration (fees, branding, deadlines, institution info).
Adds flags for controlled configuration (public visibility, editability, sort order).

Architecture:
- Flexible key/value structure with type and group.
- Officers manage settings via Filament (edit only, no create/delete).
- Prevents hardcoding values like fees, colours, deadlines.
- Supports typed values and grouped categories.
- Flags ensure sensitive/system settings are not exposed.

Status: ✅ Production Ready
Version: 1.1 (controlled settings foundation, v5 compatible)
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // Unique key for each setting (e.g. admissions.application_start)
            $table->string('key')->unique();

            // Value stored as text, typed via 'type' column
            $table->text('value')->nullable();

            // Type of the value: string, integer, boolean, date, json
            $table->string('type')->default('string');

            // Group for structured UI: admissions, branding, institution, portal, payments
            $table->string('group')->nullable();

            // Flags for controlled configuration
            $table->boolean('is_public')->default(false);   // Safe to expose to portal
            $table->boolean('is_editable')->default(true);  // Officers can edit
            $table->integer('sort_order')->default(0);      // UI ordering

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
