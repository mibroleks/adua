<?php

/*
Component: Admission Decision History Migration
File Path: database/migrations/2026_11_03_000002_create_admission_decision_histories_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Creates an audit history table for institutional admission
outcome changes.

Records:
- Previous admission status.
- New admission status.
- Responsible officer.
- Decision remarks.
- Date and time of the change.

Architecture:
- Each history record belongs to an admission decision.
- Existing application status history remains untouched.
- Existing admission decisions are not modified.
- History is maintained through AdmissionService.

Version: 1.0
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create admission decision history table.
     */
    public function up(): void
    {
        Schema::create('admission_decision_histories', function (Blueprint $table) {
            $table->id();

            /*
             * Admission decision being tracked.
             */
            $table->foreignId('admission_decision_id')
                ->constrained('admission_decisions')
                ->cascadeOnDelete();

            /*
             * Previous admission outcome.
             *
             * Nullable because the first history record may
             * represent the initial PENDING status.
             */
            $table->enum('old_status', [
                'PENDING',
                'ADMITTED',
                'NOT_ADMITTED',
                'WAITLISTED',
                'DEFERRED',
            ])->nullable();

            /*
             * New admission outcome.
             */
            $table->enum('new_status', [
                'PENDING',
                'ADMITTED',
                'NOT_ADMITTED',
                'WAITLISTED',
                'DEFERRED',
            ]);

            /*
             * Officer responsible for the change.
             */
            $table->foreignId('officer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * Explanation or remarks associated with the change.
             */
            $table->text('remarks')->nullable();

            /*
             * Date and time the outcome changed.
             */
            $table->timestamp('changed_at')->useCurrent();

            $table->timestamps();

            /*
             * Supports chronological history retrieval.
             */
            $table->index(
                ['admission_decision_id', 'changed_at'],
                'admission_decision_history_timeline_index'
            );

            /*
             * Supports reporting by admission outcome.
             */
            $table->index(
                'new_status',
                'admission_decision_history_status_index'
            );
        });
    }

    /**
     * Drop the history table.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_decision_histories');
    }
};
