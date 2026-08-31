<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\City;
use App\Models\User;

class CityPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, City $city): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, City $city): bool
    {
        return true;
    }

    public function delete(User $user, City $city): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function deleteAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}
