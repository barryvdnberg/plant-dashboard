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
    $placeholder = $partial.' > [aria-busy]';
    $schema = $partial.' > [data-testid="conditional-schema"]:not([aria-busy])';
    $requiredInput = $schema.' input[required]';

    Livewire::test(SchemaVisibilityForm::class, ['deferContent' => true])
        ->assertPresent($placeholder)
        ->assertMissing($placeholder)
        ->fill('[id="form.mode"]', 'show')
        ->assertVisible($schema)
        ->assertNotPresent($placeholder)
        ->assertCount($partial, 1)
        ->assertEnabled($requiredInput)
        ->fill('[id="form.mode"]', 'hide')
        ->assertMissing($schema)
        ->assertDisabled($requiredInput)
        ->assertNoJavaScriptErrors();
});
