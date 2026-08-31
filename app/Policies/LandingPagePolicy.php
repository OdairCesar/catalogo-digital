<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\LandingPage;
use App\Models\User;

class LandingPagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LandingPage $landingPage): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, LandingPage $landingPage): bool
    {
        return true;
    }

    public function delete(User $user, LandingPage $landingPage): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function deleteAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}
