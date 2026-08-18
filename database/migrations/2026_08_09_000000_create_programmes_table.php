<?php

/*
Component: Programme Migration
File Path: database/migrations/2026_08_09_000000_create_programmes_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Creates the programmes table for storing academic programme data.
Each programme belongs to a department, which belongs to a faculty.
Each programme includes its own application fee so that students pay
the correct amount for the programme they select.
Supports enabling/disabling programmes for admissions.
Extended with global-standard fields: requirements, career paths,
tuition, credits, delivery mode, language, accreditation, outcomes, scholarships.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();

            // Link to department (programme belongs to a department)
            $table->foreignId('department_id')
                ->constrained('departments')
                ->restrictOnDelete();

            // Core programme info
            $table->string('name');                  // Programme name
            $table->string('code')->unique();        // Unique code e.g. CSC101
            $table->text('description')->nullable(); // Overview / objectives
            $table->integer('duration')->default(4); // Duration in years
            $table->string('degree_type')->default('BSc'); // Award type

            // Fees
            $table->unsignedBigInteger('application_fee')->default(0); // Application fee
            $table->decimal('tuition', 12, 2)->nullable();             // Tuition fees

            // Academic metadata
            $table->integer('credits')->nullable();                    // Total credits/units
            $table->string('delivery_mode')->nullable();               // Full-time, part-time, online
            $table->string('language')->default('English');            // Language of instruction
            $table->string('accreditation_body')->nullable();          // Accrediting body (NUC, NBTE, etc.)

            // Extended descriptive fields
            $table->text('requirements')->nullable();                  // Admission requirements
            $table->text('career_paths')->nullable();                  // Career opportunities
            $table->text('outcomes')->nullable();                      // Programme outcomes / competencies
            $table->text('scholarships')->nullable();                  // Scholarships / funding options

            // Status flags
            $table->boolean('active')->default(true);                  // General status
            $table->boolean('application_enabled')->default(true);     // Admissions availability

            // Laravel timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};
