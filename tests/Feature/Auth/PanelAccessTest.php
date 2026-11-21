<?php

use App\Models\User;
use Filament\Panel;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('restricts the admin panel to administrators', function (): void {
    $user = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $panel = Panel::make()->id('admin');

    expect($user->canAccessPanel($panel))->toBeFalse()
        ->and($admin->canAccessPanel($panel))->toBeTrue();
});

it('allows authenticated users into the app panel only', function (): void {
    $user = User::factory()->create();
    $panel = Panel::make()->id('app');

    expect($user->canAccessPanel($panel))->toBeTrue();
});

it('denies unrecognized panels', function (): void {
    $user = User::factory()->create();
    $panel = Panel::make()->id('unknown');

    expect($user->canAccessPanel($panel))->toBeFalse();
});

it('redirects guests to the admin login page', function (): void {
    get('/admin')->assertRedirect('/admin/login');
});

it('forbids regular users from the admin panel', function (): void {
    actingAs(User::factory()->create());

    get('/admin')->assertForbidden();
});

it('allows administrators into the admin panel', function (): void {
    actingAs(User::factory()->admin()->create());

    get('/admin')->assertOk();
});

it('allows authenticated users into the app panel', function (): void {
    actingAs(User::factory()->create());

    get('/app')->assertRedirect('/app/my-actions');
    get('/app/my-actions')->assertOk();
});