<?php

use App\Models\ServiceCluster;
use App\Services\Seo\SitemapBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

test('it regenerates slugs built from a title that still has location tokens', function () {
    $cluster = ServiceCluster::factory()->create([
        'title' => 'Desenvolvimento de Sites Personalizados em {cidade}',
        'slug' => Str::slug('Desenvolvimento de Sites Personalizados em {cidade}'),
    ]);

    $this->artisan('app:fix-service-cluster-location-slugs', ['--force' => true])
        ->assertExitCode(0);

    expect($cluster->fresh()->slug)->toBe('desenvolvimento-de-sites-personalizados');
});

test('dry run reports the change without saving it', function () {
    $cluster = ServiceCluster::factory()->create([
        'title' => 'Loja Virtual para Empresas de {cidade}',
        'slug' => Str::slug('Loja Virtual para Empresas de {cidade}'),
    ]);

    $this->artisan('app:fix-service-cluster-location-slugs', ['--dry-run' => true])
        ->assertExitCode(0);

    expect($cluster->fresh()->slug)->toBe(Str::slug('Loja Virtual para Empresas de {cidade}'));
});

test('it still fixes a slug that got a numeric suffix from a collision', function () {
    $cluster = ServiceCluster::factory()->create([
        'title' => 'Loja Virtual para Empresas de {cidade}',
        'slug' => Str::slug('Loja Virtual para Empresas de {cidade}').'-2',
    ]);

    $this->artisan('app:fix-service-cluster-location-slugs', ['--force' => true])
        ->assertExitCode(0);

    expect($cluster->fresh()->slug)->toBe('loja-virtual-para-empresas');
});

test('it busts the sitemap cache when a slug changes', function () {
    Cache::put(SitemapBuilder::cacheKey(), ['stale'], now()->addHour());

    ServiceCluster::factory()->create([
        'title' => 'Loja Virtual para Empresas de {cidade}',
        'slug' => Str::slug('Loja Virtual para Empresas de {cidade}'),
    ]);

    $this->artisan('app:fix-service-cluster-location-slugs', ['--force' => true])
        ->assertExitCode(0);

    expect(Cache::has(SitemapBuilder::cacheKey()))->toBeFalse();
});

test('it skips clusters whose slug was already manually edited', function () {
    $cluster = ServiceCluster::factory()->create([
        'title' => 'Loja Virtual para Empresas de {cidade}',
        'slug' => 'loja-virtual-customizada',
    ]);

    $this->artisan('app:fix-service-cluster-location-slugs', ['--force' => true])
        ->assertExitCode(0);

    expect($cluster->fresh()->slug)->toBe('loja-virtual-customizada');
});

test('it leaves clusters without location tokens untouched', function () {
    $cluster = ServiceCluster::factory()->create([
        'title' => 'Loja Virtual Premium',
        'slug' => 'loja-virtual-premium',
    ]);

    $this->artisan('app:fix-service-cluster-location-slugs', ['--force' => true])
        ->assertExitCode(0);

    expect($cluster->fresh()->slug)->toBe('loja-virtual-premium');
});
