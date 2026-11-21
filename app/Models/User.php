<?php

namespace App\Models;

use App\Enums\NameDisplay;
use App\Enums\NameDisplayGroup;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'name_display_preference',
        'name_display_overrides',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'password' => 'hashed',
            'name_display_preference' => NameDisplay::class,
            'name_display_overrides' => 'array',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->is_admin,
            'app' => true,
            default => false,
        };
    }

    public function adminLocale(): string
    {
        return array_key_exists($this->locale ?? '', config('localization.locales'))
            ? $this->locale
            : config('app.locale');
    }

    public function nameDisplayFor(NameDisplayGroup $group): NameDisplay
    {
        $override = $this->name_display_overrides[$group->value] ?? null;

        return (is_string($override) ? NameDisplay::tryFrom($override) : null)
            ?? $this->name_display_preference
            ?? NameDisplay::Scientific;
    }

    public function gardens(): HasMany
    {
        return $this->hasMany(related: Garden::class);
    }
}
