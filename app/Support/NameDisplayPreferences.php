<?php

namespace App\Support;

use App\Enums\NameDisplay;
use App\Enums\NameDisplayGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** Activated by admin middleware only; models remain independent of Filament and auth. */
class NameDisplayPreferences
{
    protected ?User $user = null;

    public function useUser(?User $user): void
    {
        $this->user = $user;
    }

    public function forModel(Model $model): ?NameDisplay
    {
        $group = NameDisplayGroup::forModel($model);

        return $group && $this->user ? $this->user->nameDisplayFor($group) : null;
    }
}
