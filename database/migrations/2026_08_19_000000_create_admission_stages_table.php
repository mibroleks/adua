<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Application Submitted", "Payment", "Documents", "Decision"
            $table->text('description')->nullable();
            $table->unsignedInteger('order')->default(0); // sequence order
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_stages');
    }
};
