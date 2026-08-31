<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfills `portfolio_items` rows into the generic `sections` table
 * (type=portfolio) as part of retiring the standalone `PortfolioItem` model.
 * The `portfolio_items` table itself is intentionally left in place — it can
 * be dropped in a later cleanup migration once the new setup is confirmed
 * working.
 */
return new class extends Migration
{
    public function up(): void
    {
        $items = DB::table('portfolio_items')->get();

        foreach ($items as $item) {
            DB::table('sections')->insert([
                'type' => 'portfolio',
                'service_id' => $item->service_id,
                'title' => $item->title,
                'slug' => $item->slug,
                'excerpt' => $item->excerpt,
                'content' => $item->content,
                'image' => $item->cover_image,
                'meta_title' => $item->meta_title,
                'meta_description' => $item->meta_description,
                'canonical' => $item->canonical,
                'robots' => $item->robots,
                'status' => $item->status,
                'sort_order' => 0,
                'data' => json_encode(['external_url' => $item->external_url]),
                'extra_fields' => null,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('sections')->where('type', 'portfolio')->delete();
    }
};
