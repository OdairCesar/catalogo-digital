<?php

use App\Enums\PageStatus;
use App\Models\City;
use App\Models\State;

test('state show page resolves for a published state', function () {
    $state = State::factory()->create(['status' => PageStatus::Published]);

    $this->get(route('states.show', $state))->assertOk();
});

test('state show page 404s for a draft state', function () {
    $state = State::factory()->create(['status' => PageStatus::Draft]);

    $this->get(route('states.show', $state))->assertNotFound();
});

test('state show page only lists its own published cities', function () {
    $state = State::factory()->create(['status' => PageStatus::Published]);
    $otherState = State::factory()->create(['status' => PageStatus::Published]);

    $city = City::factory()->create(['state_id' => $state->id, 'status' => PageStatus::Published, 'name' => 'Cidade Vinculada']);
    City::factory()->create(['state_id' => $otherState->id, 'status' => PageStatus::Published, 'name' => 'Cidade De Outro Estado']);
    City::factory()->create(['state_id' => $state->id, 'status' => PageStatus::Draft, 'name' => 'Cidade Rascunho']);

    $this->get(route('states.show', $state))
        ->assertOk()
        ->assertSee($city->name)
        ->assertDontSee('Cidade De Outro Estado')
        ->assertDontSee('Cidade Rascunho');
});
