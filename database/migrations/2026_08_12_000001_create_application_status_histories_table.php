<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->id();

            // Link to application
            $table->foreignId('application_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Status change
            $table->string('old_status')->nullable();
            $table->string('new_status');

            // Officer or system user who made the change
            $table->foreignId('changed_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            // Timestamp of change
            $table->timestamp('changed_at')->useCurrent();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_status_histories');
    }
};
