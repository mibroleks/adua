<?php

/*
Component: Faculty Migration
File Path: database/migrations/2026_08_08_000000_create_faculties_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Creates the faculties table for storing academic faculties.
Faculties are lightweight taxonomy entities used to group departments.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();

            // Faculty name (e.g. Faculty of Applied Sciences)
            $table->string('name');

            // Optional short code (unique)
            $table->string('code')->nullable()->unique();

            // Description of the faculty
            $table->text('description')->nullable();

            // Active flag
            $table->boolean('active')->default(true);

            // Sort order for display
            $table->unsignedInteger('sort_order')->default(0);

            // Laravel timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculties');
    }
};
