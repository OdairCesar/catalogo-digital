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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('site_name')->nullable()->after('name');
            $table->string('whatsapp')->nullable()->after('cnpj');
            $table->string('email')->nullable()->after('whatsapp');
            $table->string('address_zip_code')->nullable()->after('email');
            $table->string('address_street')->nullable()->after('address_zip_code');
            $table->string('address_number')->nullable()->after('address_street');
            $table->string('address_complement')->nullable()->after('address_number');
            $table->string('address_neighborhood')->nullable()->after('address_complement');
            $table->string('address_city')->nullable()->after('address_neighborhood');
            $table->string('address_state', 2)->nullable()->after('address_city');
            $table->string('instagram_url')->nullable()->after('address_state');
            $table->string('facebook_url')->nullable()->after('instagram_url');
            $table->json('opening_hours')->nullable()->after('facebook_url');
            $table->text('short_description')->nullable()->after('opening_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'site_name',
                'whatsapp',
                'email',
                'address_zip_code',
                'address_street',
                'address_number',
                'address_complement',
                'address_neighborhood',
                'address_city',
                'address_state',
                'instagram_url',
                'facebook_url',
                'opening_hours',
                'short_description',
            ]);
        });
    }
};
