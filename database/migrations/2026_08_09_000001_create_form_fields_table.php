<?php

/*
Component: FormField Migration
File Path: database/migrations/2026_08_09_000004_create_form_fields_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Creates the form_fields table for storing dynamic application fields.
Fields can be global (programme_id = null) or programme-specific.
Supports multiple input types, validation rules, and display options.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();

            // Link to programme (nullable = global field)
            $table->foreignId('programme_id')
                  ->nullable()
                  ->constrained('programmes')
                  ->restrictOnDelete(); // safer than cascade

            // Field label (e.g. "Date of Birth")
            $table->string('label');

            // Unique key for internal reference
            $table->string('key')->unique();

            // Field type (text, textarea, email, phone, date, number, select, radio, checkbox)
            $table->string('type');

            // Options for select/radio/checkbox stored as JSON
            $table->json('options')->nullable();

            // Section grouping (e.g. "Personal Information")
            $table->string('section')->nullable();

            // Required flag
            $table->boolean('required')->default(false);

            // Validation rules (Laravel validation syntax)
            $table->string('validation_rules')->nullable();

            // Placeholder text
            $table->string('placeholder')->nullable();

            // Help text for applicants
            $table->text('help_text')->nullable();

            // Sort order for display
            $table->integer('sort_order')->default(0);

            // Active/inactive flag
            $table->boolean('active')->default(true);

            // Laravel timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
