<?php

/*
Component: Admission Outcome Fields Migration
File Path: database/migrations/2026_11_03_000001_add_admission_outcome_fields_to_admission_decisions_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Extends the existing admission_decisions table to support
the institution's actual admission outcome separately from
the existing application-processing decision.

Preserves:
- Existing application decisions.
- Existing officer references.
- Existing remarks and decision timestamps.
- Existing admission decision records.

New capabilities:
- Admission status.
- Admission decision officer.
- Admission decision timestamp.
- Admission-specific remarks.

Important:
Existing decision records are not automatically reinterpreted
as actual admission outcomes.

Version: 1.0
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add institutional admission outcome fields.
     */
    public function up(): void
    {
        Schema::table('admission_decisions', function (Blueprint $table) {

            /*
             * Actual institutional admission outcome.
             *
             * Nullable deliberately:
             * Existing records represent application-processing
             * decisions and must not automatically become admissions.
             */
            $table->enum('admission_status', [
                'PENDING',
                'ADMITTED',
                'NOT_ADMITTED',
                'WAITLISTED',
                'DEFERRED',
            ])->nullable()->after('decided_at');

            /*
             * Officer responsible for the actual admission decision.
             */
            $table->foreignId('admission_officer_id')
                ->nullable()
                ->after('admission_status')
                ->constrained('users')
                ->nullOnDelete();

            /*
             * Date and time of the actual admission decision.
             */
            $table->timestamp('admission_decided_at')
                ->nullable()
                ->after('admission_officer_id');

            /*
             * Remarks specifically relating to the admission outcome.
             */
            $table->text('admission_remarks')
                ->nullable()
                ->after('admission_decided_at');

            /*
             * Index for admission decision workspace filtering.
             */
            $table->index(
                'admission_status',
                'admission_decisions_status_index'
            );
        });
    }

    /**
     * Remove only the fields introduced by this migration.
     *
     * Existing application decision data remains untouched.
     */
    public function down(): void
    {
        Schema::table('admission_decisions', function (Blueprint $table) {

            $table->dropIndex(
                'admission_decisions_status_index'
            );

            $table->dropConstrainedForeignId(
                'admission_officer_id'
            );

            $table->dropColumn([
                'admission_status',
                'admission_decided_at',
                'admission_remarks',
            ]);
        });
    }
};
