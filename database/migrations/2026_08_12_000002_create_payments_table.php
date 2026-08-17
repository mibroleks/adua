<?php

/*
Component: Payments Migration
File Path: database/migrations/2026_08_12_000002_create_payments_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Creates the payments table for storing application payment transactions.
Each payment is linked to an application, records the gateway reference,
amount, currency, status, gateway provider, metadata, and timestamps.
Supports server-side verification with verified_at.

Status: ✅ Production Ready
Version: 1.3 (Development Stage Hardened)
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Link to application (required at this stage)
            $table->foreignId('application_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Gateway reference (Paystack, Flutterwave, etc.)
            $table->string('reference')->unique();

            // Internal transaction reference for auditing
            $table->string('transaction_reference')->nullable();

            // Payment type (Application Fee, Acceptance Fee, etc.)
            $table->string('payment_type')->default('APPLICATION_FEE');

            // Amount stored in minor units (kobo for NGN)
            $table->unsignedBigInteger('amount');

            $table->string('currency')->default('NGN');

            // Payment status: PENDING, SUCCESS, FAILED
            $table->string('status')->default('PENDING');

            // Gateway name (future-proofing for multiple providers)
            $table->string('gateway')->default('paystack');

            // Optional metadata (JSON for extra info)
            $table->json('metadata')->nullable();

            // Timestamp when payment was marked successful
            $table->timestamp('paid_at')->nullable();

            // Timestamp when payment was verified server-side
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
