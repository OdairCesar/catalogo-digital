<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ServiceCluster;
use App\Models\User;

class ServiceClusterPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceCluster $serviceCluster): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ServiceCluster $serviceCluster): bool
    {
        return true;
    }

    public function delete(User $user, ServiceCluster $serviceCluster): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function deleteAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}
