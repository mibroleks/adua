<?php

/*
Component: Alter Application Fee Column
File Path: database/migrations/2026_08_19_000003_alter_application_fee_column_on_applications_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Updates the applications table to change application_fee
from unsignedBigInteger to decimal(10,2).
This aligns with the Application model's decimal:2 cast
and prevents invalid input errors in Postgres/MySQL.
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
            // Change application_fee to decimal(10,2)
            $table->decimal('application_fee', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Revert back to unsignedBigInteger if needed
            $table->unsignedBigInteger('application_fee')->nullable()->change();
        });
    }
};
