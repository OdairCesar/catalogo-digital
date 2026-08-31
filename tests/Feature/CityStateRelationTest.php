<?php

use App\Models\City;
use App\Models\State;

test('a city resolves its linked state record through the state_id foreign key', function () {
    $state = State::factory()->create();
    $city = City::factory()->create(['state_id' => $state->id]);

    expect($city->state)->not->toBeNull()
        ->and($city->state->is($state))->toBeTrue();
});

test('a city without a linked state returns null for the state record', function () {
    $city = City::factory()->create(['state_id' => null]);

    expect($city->state)->toBeNull();
});
