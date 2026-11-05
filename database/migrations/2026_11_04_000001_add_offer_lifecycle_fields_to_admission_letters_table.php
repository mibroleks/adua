<?php

/*
Component: Add Offer Lifecycle Fields To Admission Letters Table
File Path: database/migrations/2026_11_04_000001_add_offer_lifecycle_fields_to_admission_letters_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Extends the existing admission_letters table to support
versioned and historically preserved official admission offers.

Responsibilities:
- Preserve existing admission letter records and fields.
- Store immutable document content snapshots.
- Track offer versions and lifecycle status.
- Record withdrawal and supersession information.

Architecture:
- AdmissionLetter
- AdmissionDecision
- User

Status: New
Version: 1.0
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_letters', function (Blueprint $table) {
            $table->unsignedSmallInteger('version')
                ->default(1)
                ->after('file_path');

            $table->string('status', 20)
                ->default('DRAFT')
                ->after('version');

            $table->json('content_snapshot')
                ->nullable()
                ->after('status');

            $table->timestamp('withdrawn_at')
                ->nullable()
                ->after('content_snapshot');

            $table->foreignId('withdrawn_by')
                ->nullable()
                ->after('withdrawn_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->text('withdrawal_reason')
                ->nullable()
                ->after('withdrawn_by');

            $table->timestamp('superseded_at')
                ->nullable()
                ->after('withdrawal_reason');

            $table->index(
                ['admission_decision_id', 'status'],
                'admission_letters_decision_status_index'
            );
        });

        /*
         * Preserve the existing state of historical letters.
         *
         * Previously issued or published letters are marked ISSUED.
         * Records without either date remain DRAFT.
         */
        DB::table('admission_letters')
            ->where(function ($query) {
                $query->whereNotNull('issued_at')
                    ->orWhereNotNull('published_at');
            })
            ->update([
                'status' => 'ISSUED',
            ]);
    }

    public function down(): void
    {
        Schema::table('admission_letters', function (Blueprint $table) {
            $table->dropIndex(
                'admission_letters_decision_status_index'
            );

            $table->dropConstrainedForeignId('withdrawn_by');

            $table->dropColumn([
                'version',
                'status',
                'content_snapshot',
                'withdrawn_at',
                'withdrawal_reason',
                'superseded_at',
            ]);
        });
    }
};
