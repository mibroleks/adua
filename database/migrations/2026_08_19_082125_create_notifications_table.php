<?php

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
        Schema::create('notifications', function (Blueprint $table) {
            // Use UUID for notifications (default Laravel convention)
            $table->uuid('id')->primary();

            // Notification type (class name)
            $table->string('type');

            // Polymorphic relation: notifiable_type + notifiable_id
            $table->morphs('notifiable');

            // JSON/text payload
            $table->text('data');

            // When the notification was read
            $table->timestamp('read_at')->nullable();

            // Created_at / updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
