<?php

use App\Models\Category;
use App\Models\City;
use App\Models\LandingPage;
use App\Models\Post;
use App\Models\Section;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Models\State;
use App\Models\User;

dataset('content models', [
    'Post' => fn () => Post::factory()->create(),
    'Service' => fn () => Service::factory()->create(),
    'ServiceCluster' => fn () => ServiceCluster::factory()->create(['service_id' => Service::factory()->create()->id]),
    'City' => fn () => City::factory()->create(),
    'State' => fn () => State::factory()->create(),
    'Category' => fn () => Category::factory()->create(),
    'Section (Portfolio)' => fn () => Section::factory()->portfolio()->create(),
    'LandingPage' => function (): LandingPage {
        $service = Service::factory()->create();
        $city = City::factory()->create();

        return LandingPage::where('service_id', $service->id)->where('city_id', $city->id)->sole();
    },
    'ServiceClusterLandingPage' => function () {
        $cluster = ServiceCluster::factory()->create(['service_id' => Service::factory()->create()->id]);
        $city = City::factory()->create();

        return $cluster->landingPages()->where('city_id', $city->id)->sole();
    },
]);

test('editor can view, create and update content models but cannot delete them', function (Closure $makeModel) {
    $editor = User::factory()->create();
    $model = $makeModel();

    expect($editor->can('viewAny', $model::class))->toBeTrue()
        ->and($editor->can('view', $model))->toBeTrue()
        ->and($editor->can('create', $model::class))->toBeTrue()
        ->and($editor->can('update', $model))->toBeTrue()
        ->and($editor->can('delete', $model))->toBeFalse()
        ->and($editor->can('deleteAny', $model::class))->toBeFalse();
})->with('content models');

test('admin has unrestricted access to content models, including delete', function (Closure $makeModel) {
    $admin = User::factory()->admin()->create();
    $model = $makeModel();

    expect($admin->can('viewAny', $model::class))->toBeTrue()
        ->and($admin->can('view', $model))->toBeTrue()
        ->and($admin->can('create', $model::class))->toBeTrue()
        ->and($admin->can('update', $model))->toBeTrue()
        ->and($admin->can('delete', $model))->toBeTrue()
        ->and($admin->can('deleteAny', $model::class))->toBeTrue();
})->with('content models');
