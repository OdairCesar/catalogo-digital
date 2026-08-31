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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('sku')->nullable();
            $table->longText('description')->nullable();
            $table->string('brand')->nullable();
            $table->string('gtin')->nullable();
            $table->string('mpn')->nullable();
            $table->string('condition')->default('new');
            $table->string('product_type')->nullable();
            $table->string('google_product_category')->nullable();
            $table->decimal('base_price', 10, 2)->nullable();
            $table->decimal('base_sale_price', 10, 2)->nullable();
            $table->integer('base_stock')->nullable();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->decimal('height_cm', 8, 2)->nullable();
            $table->decimal('width_cm', 8, 2)->nullable();
            $table->decimal('length_cm', 8, 2)->nullable();
            $table->string('cover_image')->nullable();
            $table->json('images')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
