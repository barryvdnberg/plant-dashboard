<?php

namespace App\Policies;

use App\Models\Garden;
use App\Models\User;

class GardenPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function assignOwner(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, Garden $garden): bool
    {
        return $user->is_admin || (int) $garden->user_id === (int) $user->id;
    }

    public function update(User $user, Garden $garden): bool
    {
        return $this->view($user, $garden);
    }

    public function delete(User $user, Garden $garden): bool
    {
        return $this->update($user, $garden);
    }

    public function deleteAny(User $user): bool
    {
        return $user->is_admin;
    }
}
