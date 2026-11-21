<?php

namespace App\Policies;

use App\Models\GardenPlant;
use App\Models\User;

class GardenPlantPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    // The relation manager also authorizes the destination garden.
    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, GardenPlant $plant): bool
    {
        return $user->is_admin || $plant->garden()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, GardenPlant $plant): bool
    {
        return $this->view($user, $plant);
    }

    public function delete(User $user, GardenPlant $plant): bool
    {
        return $this->update($user, $plant);
    }

    public function deleteAny(User $user): bool
    {
        return true;
    }
}
