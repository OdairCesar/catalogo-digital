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
        Schema::table('service_cluster_landing_pages', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('city_id');
        });

        $rows = DB::table('service_cluster_landing_pages')
            ->join('service_clusters', 'service_clusters.id', '=', 'service_cluster_landing_pages.service_cluster_id')
            ->join('cities', 'cities.id', '=', 'service_cluster_landing_pages.city_id')
            ->whereNull('service_cluster_landing_pages.slug')
            ->select([
                'service_cluster_landing_pages.id as pivot_id',
                'service_clusters.slug as cluster_slug',
                'cities.slug as city_slug',
            ])
            ->get();

        foreach ($rows as $row) {
            DB::table('service_cluster_landing_pages')
                ->where('id', $row->pivot_id)
                ->update(['slug' => "{$row->cluster_slug}-em-{$row->city_slug}"]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_cluster_landing_pages', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
