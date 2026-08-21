<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_document_histories', function (Blueprint $table) {
            $table->id();

            // Link to the document
            $table->foreignId('application_document_id')
                ->constrained('application_documents')
                ->cascadeOnDelete();

            // Link to the application
            $table->foreignId('application_id')
                ->constrained()
                ->cascadeOnDelete();

            // Lifecycle action: UPLOADED, VERIFIED, REJECTED, REPLACED
            $table->string('action');

            // Status before and after the action
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();

            // Officer or student who performed the action
            $table->foreignId('performed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Remarks (e.g. rejection reason)
            $table->text('remarks')->nullable();

            // When the action was performed
            $table->timestamp('performed_at')->useCurrent();

            $table->timestamps();

            // Index for fast history lookups
            $table->index(['application_document_id', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_document_histories');
    }
};
