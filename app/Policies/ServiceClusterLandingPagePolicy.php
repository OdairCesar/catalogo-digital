<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ServiceClusterLandingPage;
use App\Models\User;

class ServiceClusterLandingPagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceClusterLandingPage $serviceClusterLandingPage): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ServiceClusterLandingPage $serviceClusterLandingPage): bool
    {
        return true;
    }

    public function delete(User $user, ServiceClusterLandingPage $serviceClusterLandingPage): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function deleteAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}
