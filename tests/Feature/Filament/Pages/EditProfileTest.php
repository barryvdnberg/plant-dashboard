<?php

use App\Enums\NameDisplay;
use App\Enums\NameDisplayGroup;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Resources\Cultivars\Pages\ListCultivars;
use App\Models\Cultivar;
use App\Models\Language;
use App\Models\Plant;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\postJson;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    config(['app.locale' => 'en']);
    app()->setLocale('en');
    Filament::setCurrentPanel('admin');
    Filament::bootCurrentPanel();
});

it('registers the built-in profile only in the admin panel', function (): void {
    expect(Filament::getPanel('admin')->getProfilePage())->toBe(EditProfile::class)
        ->and(Filament::getPanel('admin')->isProfilePageSimple())->toBeFalse()
        ->and(Filament::getPanel('app')->hasProfile())->toBeFalse();

    get('/admin/profile')->assertRedirect('/admin/login');
    actingAs(User::factory()->create());
    get('/admin/profile')->assertForbidden();
    actingAs(User::factory()->admin()->create());
    get('/admin/profile')->assertOk()->assertSee('Regional settings')->assertSee('Display by group');
});

it('loads and saves the current administrators language and display preferences', function (): void {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->admin()->create();
    actingAs($admin);

    livewire(EditProfile::class)
        ->assertFormSet(['name_display_preference' => NameDisplay::Scientific])
        ->fillForm([
            'locale' => 'nl',
            'name_display_preference' => 'scientific',
            'name_display_overrides' => ['cultivars' => 'translated', 'plants' => null],
        ])
        ->set('data.id', $other->id)
        ->set('data.is_admin', false)
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect(EditProfile::getUrl(panel: 'admin'));

    expect($admin->fresh()->locale)->toBe('nl')
        ->and($admin->fresh()->name_display_preference)->toBe(NameDisplay::Scientific)
        ->and($admin->fresh()->name_display_overrides)->toBe(['cultivars' => 'translated'])
        ->and($admin->fresh()->is_admin)->toBeTrue()
        ->and($other->fresh()->locale)->toBeNull();

    livewire(EditProfile::class)->assertFormSet([
        'locale' => 'nl',
        'name_display_overrides.cultivars' => NameDisplay::Translated,
    ]);
});

it('clears overrides to inherit the global choice', function (): void {
    $admin = User::factory()->admin()->create([
        'name_display_preference' => NameDisplay::Translated,
        'name_display_overrides' => ['cultivars' => 'scientific'],
    ]);
    actingAs($admin);

    livewire(EditProfile::class)
        ->fillForm(['name_display_overrides' => ['cultivars' => null]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($admin->fresh()->name_display_overrides)->toBeNull()
        ->and($admin->fresh()->nameDisplayFor(NameDisplayGroup::Cultivars))->toBe(NameDisplay::Translated);
});

it('rejects unsupported locales and display choices', function (array $data, array $errors): void {
    $admin = User::factory()->admin()->create();
    actingAs($admin);

    livewire(EditProfile::class)->fillForm($data)->call('save')->assertHasFormErrors($errors);

    expect($admin->fresh()->locale)->toBeNull()
        ->and($admin->fresh()->name_display_overrides)->toBeNull();
})->with([
    [['locale' => 'unsupported'], ['locale']],
    [['name_display_preference' => 'invalid'], ['name_display_preference']],
    [['name_display_overrides' => ['cultivars' => 'invalid']], ['name_display_overrides.cultivars']],
]);

it('denies profile updates after administrator access is revoked', function (): void {
    $admin = User::factory()->admin()->create();
    actingAs($admin);
    $page = livewire(EditProfile::class)->fillForm(['locale' => 'nl']);
    $admin->forceFill(['is_admin' => false])->save();

    $page->call('save')->assertForbidden();
    expect($admin->fresh()->locale)->toBeNull();
});

it('applies the language on initial and subsequent Livewire admin requests only', function (): void {
    actingAs(User::factory()->admin()->create(['locale' => 'nl']));

    $response = get('/admin/profile')->assertOk()->assertSee('Regionale instellingen');
    expect(app()->getLocale())->toBe('en');

    preg_match_all('/wire:snapshot="([^"]+)"/', $response->getContent(), $matches);
    $snapshot = collect($matches[1])
        ->map(fn ($snapshot) => html_entity_decode($snapshot, ENT_QUOTES))
        ->first(fn ($snapshot) => json_decode($snapshot, true)['memo']['name'] === EditProfile::class);
    expect($snapshot)->not->toBeNull();

    postJson(route('default-livewire.update'), [
        'components' => [['snapshot' => $snapshot, 'updates' => [], 'calls' => []]],
    ], ['X-Livewire' => 'true'])
        ->assertOk()
        ->assertJsonPath('components.0.effects.html', fn ($html) => str_contains($html, 'Regionale instellingen'));

    expect(app()->getLocale())->toBe('en');
    get('/app/my-actions')->assertOk();
    expect(app()->getLocale())->toBe('en');
});

it('keeps the display preference active while Livewire renders table records', function (): void {
    createRequiredLanguages();
    $plant = Plant::factory()->create(['name' => 'Prunus yedoensis']);
    $cultivar = Cultivar::factory()->for($plant)->create(['name' => 'Akebono']);
    $cultivar->localizations()->create(['language_id' => Language::whereCode('nl')->value('id'), 'value' => 'Sierkers']);
    actingAs(User::factory()->admin()->create(['locale' => 'nl', 'name_display_preference' => NameDisplay::Scientific]));

    $response = get('/admin/cultivars')->assertOk()->assertSee("Prunus yedoensis 'Akebono'");
    preg_match_all('/wire:snapshot="([^"]+)"/', $response->getContent(), $matches);
    $snapshot = collect($matches[1])
        ->map(fn ($snapshot) => html_entity_decode($snapshot, ENT_QUOTES))
        ->first(fn ($snapshot) => json_decode($snapshot, true)['memo']['name'] === ListCultivars::class);
    expect($snapshot)->not->toBeNull();

    postJson(route('default-livewire.update'), [
        'components' => [['snapshot' => $snapshot, 'updates' => [], 'calls' => []]],
    ], ['X-Livewire' => 'true'])
        ->assertOk()
        ->assertJsonPath('components.0.effects.html', fn ($html) => str_contains($html, e("Prunus yedoensis 'Akebono'")));

    expect($cultivar->display_name)->toBe('Prunus yedoensis Akebono');
});
