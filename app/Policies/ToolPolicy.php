<?php

namespace App\Policies;

use App\Models\Tool;
use App\Models\User;

class ToolPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Tool $tool)
    {
        return $user->id === $tool->user_id;
    }

    public function update(User $user, Tool $tool)
    {
        return $user->id === $tool->user_id;
    }

    public function delete(User $user, Tool $tool)
    {
        return $user->id === $tool->user_id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function restore(User $user, Tool $tool): bool
    {
        return false;
    }

    public function forceDelete(User $user, Tool $tool): bool
    {
        return false;
    }
}
