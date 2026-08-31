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
        Schema::table('sections', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('type')->constrained()->nullOnDelete();
            $table->string('slug')->nullable()->unique()->after('title');
            $table->text('excerpt')->nullable()->after('content');
            $table->string('meta_title')->nullable()->after('excerpt');
            $table->string('meta_description')->nullable()->after('meta_title');
            $table->string('canonical')->nullable()->after('meta_description');
            $table->string('robots')->nullable()->after('canonical');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_id');
            $table->dropColumn(['slug', 'excerpt', 'meta_title', 'meta_description', 'canonical', 'robots']);
        });
    }
};
