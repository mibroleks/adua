<?php

/*
Component: ApplicationDocumentType Migration
File Path: database/migrations/2026_08_09_000005_create_application_document_types_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Creates the application_document_types table for defining required or optional application documents.
Supports name, key, required flag, allowed file types, maximum size, active status,
and programme linkage (nullable for global requirements).
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('application_document_types', function (Blueprint $table) {
            $table->id();

            // Link to programme (nullable = global requirement)
            $table->foreignId('programme_id')
                  ->nullable()
                  ->constrained('programmes')
                  ->restrictOnDelete(); // safer than cascade

            // Document name (e.g. Passport Photograph)
            $table->string('name');

            // Unique key for internal reference (e.g. passport_photo)
            $table->string('key')->unique();

            // Required flag
            $table->boolean('required')->default(false);

            // Allowed file types stored as JSON (e.g. ["jpg","png","pdf"])
            $table->json('allowed_file_types')->nullable();

            // Maximum file size in KB
            $table->integer('max_size')->nullable();

            // Active/inactive flag
            $table->boolean('active')->default(true);

            // Laravel timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_document_types');
    }
};
