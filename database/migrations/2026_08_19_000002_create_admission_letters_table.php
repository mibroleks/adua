<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('admission_decision_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('letter_number')->unique();
            $table->string('type')->default('OFFER'); // e.g. OFFER, CONDITIONAL, REJECTION
            $table->foreignId('issued_by')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('file_path')->nullable(); // optional storage path for PDF/printable file
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_letters');
    }
};
