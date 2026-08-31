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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->json('initial_answers')->nullable();
            $table->json('messages')->nullable();
            $table->string('status');
            $table->unsignedInteger('questions_asked')->default(0);
            $table->json('report')->nullable();
            $table->string('ai_model')->nullable();
            $table->text('ai_error')->nullable();
            $table->string('source_url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
