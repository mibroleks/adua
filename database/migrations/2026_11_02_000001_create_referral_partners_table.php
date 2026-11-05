<?php

/*
Component: Referral Partners Migration
File Path: database/migrations/2026_11_02_000001_create_referral_partners_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Creates the registry of approved recruitment and referral partners.

Architecture:
- Each partner has a unique referral code.
- Only active partners can generate referral attribution.
- Partner records are retained for historical application attribution.
- Referral partners do not participate in admission or payment decisions.

Status: Implementation
Version: 1.0 (initial referral partner registry)
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
        Schema::create('referral_partners', function (Blueprint $table) {
            $table->id();

            // Official partner name.
            $table->string('name');

            // Unique code used in referral URLs.
            $table->string('code', 100)->unique();

            // Controls whether new referrals can be attributed to this partner.
            $table->boolean('is_active')->default(true);

            // Optional internal administrative notes.
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_partners');
    }
};