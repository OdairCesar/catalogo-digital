<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('cities')
            ->whereNull('state_id')
            ->whereNotNull('uf')
            ->get(['id', 'uf'])
            ->each(function (object $city): void {
                $stateId = DB::table('states')->where('uf', $city->uf)->value('id');

                if ($stateId !== null) {
                    DB::table('cities')->where('id', $city->id)->update(['state_id' => $stateId]);
                }
            });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn(['state', 'uf']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->string('state')->after('name');
            $table->string('uf', 2)->after('state');
        });
    }
};
