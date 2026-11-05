<?php

/*
Component: Add Referral Partner to Users Migration
File Path: database/migrations/2026_11_02_000002_add_referral_partner_id_to_users_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Adds referral attribution to registered user accounts.

Architecture:
- A user may be associated with one referral partner.
- Referral attribution is optional.
- Existing users remain unaffected.
- Referral partner records are protected from accidental deletion
  while referenced by users.
- Referral attribution does not participate in admission
  or payment decisions.

Status: Implementation
Version: 1.0 (initial user referral attribution)
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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('referral_partner_id')
                ->nullable()
                ->after('role')
                ->constrained('referral_partners')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referral_partner_id']);
            $table->dropColumn('referral_partner_id');
        });
    }
};
