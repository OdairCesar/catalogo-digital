<?php

use App\Enums\UserRole;
use App\Models\User;
use Filament\Facades\Filament;

test('admins and editors can access the admin panel', function (UserRole $role) {
    $user = User::factory()->make(['role' => $role]);

    expect($user->canAccessPanel(Filament::getDefaultPanel()))->toBeTrue();
})->with([
    'admin' => UserRole::Admin,
    'editor' => UserRole::Editor,
]);

test('a user without a recognised role cannot access the admin panel', function () {
    $user = User::factory()->make(['role' => null]);

    expect($user->canAccessPanel(Filament::getDefaultPanel()))->toBeFalse();
});
