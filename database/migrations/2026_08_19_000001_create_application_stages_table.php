<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('admission_stage_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('status')->default('PENDING'); // e.g. PENDING, IN_PROGRESS, COMPLETED
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_stages');
    }
};
