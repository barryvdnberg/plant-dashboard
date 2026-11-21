<?php

use Pest\Browser\Api\Livewire;
use Tests\Fixtures\Livewire\TabBuilderForm;

it('renders fixed-key tabs and selects a valid tab after removal', function (): void {
    $page = Livewire::test(TabBuilderForm::class, ['initialState' => ['items' => [
        'plain' => ['note' => 'General content'],
        'other' => ['other_note' => 'Other content'],
    ]]]);

    $page->resize(1280, 720)
        ->assertCount('[role="tab"]', 2)
        ->assertAttribute('[role="tab"]:first-of-type', 'aria-selected', 'true')
        ->click('[role="tab"]:nth-of-type(2)')
        ->assertAttribute('[role="tab"]:nth-of-type(2)', 'aria-selected', 'true')
        ->click('[role="tabpanel"].fi-active [data-test="delete-item"]')
        ->assertCount('[role="tab"]', 1)
        ->assertAttribute('[role="tab"]:first-of-type', 'aria-selected', 'true')
        ->assertNoJavaScriptErrors();
});

it('switches to horizontal tabs on small screens and supports keyboard navigation', function (): void {
    $page = Livewire::test(TabBuilderForm::class, ['initialState' => ['items' => [
        'plain' => ['note' => 'General content'],
        'other' => ['other_note' => 'Other content'],
    ]]]);

    $page->resize(390, 844)
        ->assertAttribute('[role="tablist"]', 'aria-orientation', 'horizontal')
        ->click('[role="tab"]:first-of-type');
    $page->script("document.querySelector('[role=tablist]').dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowRight', bubbles: true }))");
    $page->assertAttribute('[role="tab"]:nth-of-type(2)', 'aria-selected', 'true')
        ->assertNoJavaScriptErrors();
});

it('adds a predefined tab from the empty state and selects its panel', function (): void {
    $page = Livewire::test(TabBuilderForm::class);
    $page->click('[data-test="add-item"]')->assertVisible('.fi-select-input-btn')
        ->click('.fi-select-input-btn')->assertVisible('[role="option"][data-value="other"]')
        ->click('[role="option"][data-value="other"]')->click('[type="submit"]')
        ->assertCount('[role="tab"]', 1)
        ->assertAttribute('[role="tab"]:first-of-type', 'aria-selected', 'true')
        ->assertVisible('[role="tabpanel"].fi-active')
        ->assertNoJavaScriptErrors();
});
