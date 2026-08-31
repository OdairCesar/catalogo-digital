<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ToolSubmission;
use App\Models\User;

class ToolSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function view(User $user, ToolSubmission $toolSubmission): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ToolSubmission $toolSubmission): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function delete(User $user, ToolSubmission $toolSubmission): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function deleteAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}
