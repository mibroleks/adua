<?php

/*
Component: Department Migration
File Path: database/migrations/2026_08_08_000001_create_departments_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Creates the departments table for storing academic departments.
Each department belongs to a faculty and groups programmes.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();

            // Link to faculty
            $table->foreignId('faculty_id')
                ->constrained('faculties')
                ->restrictOnDelete();

            // Department name (e.g. Computer Science)
            $table->string('name');

            // Optional short code
            $table->string('code')->nullable();

            // Description of the department
            $table->text('description')->nullable();

            // Active flag
            $table->boolean('active')->default(true);

            // Sort order for display
            $table->unsignedInteger('sort_order')->default(0);

            // Laravel timestamps
            $table->timestamps();

            // Prevent duplicate department names within the same faculty
            $table->unique(['faculty_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
