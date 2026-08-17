<?php

/*
Component: Application Migration
File Path: database/migrations/2026_08_09_000002_create_applications_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Creates the central applications table.
It stores stable core information only.
Dynamic fields and documents are linked separately.
Programme fee is snapshotted at submission.
Application status and payment status are tracked independently.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            // Unique application number (e.g., ADM-2026-0001)
            $table->string('application_number')->unique();

            // Link to student user
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Link to chosen programme
            $table->foreignId('programme_id')
                ->constrained()
                ->restrictOnDelete(); // prevent accidental cascade deletes

            // Snapshot of programme fee at submission
            $table->unsignedBigInteger('application_fee')->nullable();

            // Application status (DRAFT, SUBMITTED, UNDER_REVIEW, APPROVED, REJECTED)
            $table->string('application_status')->default('DRAFT');

            // Payment status (PENDING, SUCCESS, FAILED)
            $table->string('payment_status')->default('PENDING');

            // Timestamp when submitted
            $table->timestamp('submitted_at')->nullable();

            // Laravel timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
