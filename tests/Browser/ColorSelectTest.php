<?php

use App\Models\Color;
use App\Models\Language;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Pest\Browser\Api\Livewire;
use Tests\Fixtures\Livewire\ColorSelectForm;
use Tests\Fixtures\Livewire\TabBuilderFieldsForm;

uses(RefreshDatabase::class);

it('uses named badge colors for relationship selections without dehydrating IDs', function (): void {
    Language::factory()->create(['code' => 'en']);
    $red = Color::factory()->create(['name' => 'Red', 'hex_value' => '#ff0000']);
    (new AppServiceProvider(app()))->boot();

    Livewire::test(ColorSelectForm::class)
        ->assertPresent('.fi-badge.fi-color-custom-red')
        ->assertSet('data.colors', [$red->id])
        ->click('Save colors')
        ->assertSet('saved', [])
        ->click('.fi-badge-delete-btn')
        ->assertSet('data.colors', [])
        ->click('Save colors')
        ->assertSet('saved', [])
        ->assertNoJavaScriptErrors();
});

it('renders a color relationship field and a standalone select as builder tabs', function (): void {
    createRequiredLanguages();
    $red = Color::factory()->create(['name' => 'Red', 'hex_value' => '#ff0000']);
    (new AppServiceProvider(app()))->boot();

    $page = Livewire::test(TabBuilderFieldsForm::class, ['initialState' => ['items' => [
        'colors' => ['colors' => [$red->id]],
        'testguy' => ['test' => 'first'],
    ]]]);

    $page->assertCount('[role="tab"]', 2)
        ->assertVisible('[role="tabpanel"].fi-active .fi-badge.fi-color-custom-red')
        ->click('[role="tab"]:nth-of-type(2)')
        ->assertVisible('[role="tabpanel"].fi-active .fi-select-input-btn')
        ->click('[role="tabpanel"].fi-active .fi-select-input-btn')
        ->assertVisible('[role="option"][data-value="second"]')
        ->click('[role="option"][data-value="second"]')
        ->assertSet('data.items.testguy.test', 'second')
        ->click('[role="tab"]:first-of-type')
        ->assertVisible('[role="tabpanel"].fi-active .fi-badge-delete-btn')
        ->click('[role="tabpanel"].fi-active .fi-badge-delete-btn')
        ->assertSet('data.items.colors.colors', [])
        ->assertNoJavaScriptErrors();
});
