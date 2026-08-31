<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\State;
use App\Models\User;

class StatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, State $state): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, State $state): bool
    {
        return true;
    }

    public function delete(User $user, State $state): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function deleteAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}
