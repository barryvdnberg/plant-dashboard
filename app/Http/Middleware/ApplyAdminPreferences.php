<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\NameDisplayPreferences;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyAdminPreferences
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User || ! $user->is_admin || Filament::getCurrentPanel()?->getId() !== 'admin') {
            return $next($request);
        }

        $previousLocale = app()->getLocale();
        $preferences = app(NameDisplayPreferences::class);
        $preferences->useUser($user);
        app()->setLocale($user->adminLocale());

        // Livewire replays persistent middleware before updating the component.
        // Keep the context until the real HTTP request has finished rendering.
        app()->terminating(function () use ($preferences, $previousLocale): void {
            $preferences->useUser(null);
            app()->setLocale($previousLocale);
        });

        return $next($request);
    }
}
