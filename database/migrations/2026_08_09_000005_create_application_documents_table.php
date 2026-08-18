<?php

/*
Component: ApplicationDocument Migration
File Path: database/migrations/xxxx_xx_xx_create_application_documents_table.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Creates the table for storing uploaded application documents.
Links each document to an application and a document type.
Supports storage details, review status, officer verification, and rejection reason.

Status: ✅ Production Ready
Version: 1.1
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
        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();

            // Link to application
            $table->foreignId('application_id')
                ->constrained()
                ->cascadeOnDelete();

            // Link to document type (global or programme-specific requirement)
            $table->foreignId('document_type_id')
                ->constrained('application_document_types')
                ->cascadeOnDelete();

            // Storage details
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();

            // Review status
            $table->string('status')->default('PENDING'); // PENDING, VERIFIED, REJECTED
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('verified_at')->nullable();

            // Officer who verified/rejected
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Reason for rejection
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_documents');
    }
};
