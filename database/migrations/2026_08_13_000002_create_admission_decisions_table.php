<?php

/*
Component: AdmissionDecision Migration
File Path: database/migrations/2026_08_13_000002_create_admission_decisions_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Creates the table for storing officer admission decisions.
Each decision is linked to an application and officer,
records the outcome (APPROVED/REJECTED), optional remarks,
and the timestamp of the decision.

Status: ✅ Production Ready
Version: 1.2
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admission_decisions', function (Blueprint $table) {
            $table->id();

            // Link to application
            $table->foreignId('application_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Officer who made the decision
            $table->foreignId('officer_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            // Decision outcome (APPROVED or REJECTED)
            $table->enum('decision', ['APPROVED', 'REJECTED']);

            // Optional remarks
            $table->text('remarks')->nullable();

            // Timestamp of decision
            $table->timestamp('decided_at')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_decisions');
    }
};
