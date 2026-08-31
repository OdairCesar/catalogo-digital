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
        Schema::create('service_clusters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->nullable()->unique();
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->json('benefits')->nullable();
            $table->json('faq')->nullable();
            $table->json('keywords')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('canonical')->nullable();
            $table->string('robots')->default('index,follow');
            $table->string('status')->default('generating');
            $table->json('ai_brief')->nullable();
            $table->string('ai_model')->nullable();
            $table->text('ai_error')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_clusters');
    }
};
