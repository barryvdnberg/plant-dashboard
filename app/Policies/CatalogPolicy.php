<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class CatalogPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Model $record): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function import(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, Model $record): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, Model $record): bool
    {
        return $user->is_admin;
    }

    public function deleteAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function replicate(User $user, Model $record): bool
    {
        return $user->is_admin;
    }
}
