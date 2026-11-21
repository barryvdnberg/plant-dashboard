<?php

use Pest\Browser\Api\Livewire;
use Tests\Fixtures\Livewire\SchemaVisibilityForm;

it('toggles an embedded schema while keeping its children in the between layout', function (): void {
    $schema = '[data-testid="conditional-schema"]';
    $requiredInput = $schema.' input[required]';

    $page = Livewire::test(SchemaVisibilityForm::class)
        ->assertPresent($schema)
        ->assertMissing($schema)
        ->assertDisabled($requiredInput)
        ->fill('[id="form.mode"]', 'show')
        ->assertVisible($schema)
        ->assertEnabled($requiredInput)
        ->assertCount($schema.' > div', 2);

    expect($page->script('getComputedStyle(document.querySelector('.json_encode($schema).')).justifyContent'))
        ->toBe('space-between');

    $page->fill('[id="form.mode"]', 'hide')
        ->assertMissing($schema)
        ->assertDisabled($requiredInput)
        ->assertNoJavaScriptErrors();
});

it('loads a deferred schema into the same visibility wrapper', function (): void {
    $partial = '[wire\\:partial="schema.form.mode.conditional"]';
    $schema = '[data-testid="conditional-schema"]';

    Livewire::test(SchemaVisibilityForm::class, ['deferContent' => true])
        ->assertPresent($partial.' > [aria-busy]')
        ->assertMissing($partial.' > [aria-busy]')
        ->fill('[id="form.mode"]', 'show')
        ->assertVisible($partial.' > '.$schema)
        ->assertCount($partial, 1)
        ->fill('[id="form.mode"]', 'hide')
        ->assertMissing($schema)
        ->assertNoJavaScriptErrors();
});
