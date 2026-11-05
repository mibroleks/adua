<?php

/*
Component: Add Referral Partner to Applications Migration
File Path: database/migrations/2026_11_02_000003_add_referral_partner_id_to_applications_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Adds recruitment referral attribution to application records.

Architecture:
- Each application may be associated with one referral partner.
- Referral attribution is optional.
- Existing applications remain unaffected.
- Historical attribution is protected against accidental partner deletion.
- Referral attribution does not participate in admission eligibility,
  application fees, payment processing, or admission decisions.

Status: Implementation
Version: 1.0 (initial application referral attribution)
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
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('referral_partner_id')
                ->nullable()
                ->after('user_id')
                ->constrained('referral_partners')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['referral_partner_id']);
            $table->dropColumn('referral_partner_id');
        });
    }
};
