<?php

use Pest\Browser\Api\Livewire;
use Tests\Fixtures\Livewire\MatrixFieldForm;

it('keeps matrix colors editable after setting state on the server', function (): void {
    $page = Livewire::test(MatrixFieldForm::class);
    $firstCell = '.grid-btn:first-of-type';

    $page
        ->assertSet('data.months', [0, 0, 0])
        ->assertAttributeContains($firstCell, 'class', 'fi-color-m0')
        ->click($firstCell)
        ->assertAttributeContains($firstCell, 'class', 'fi-color-m1')
        ->assertSet('data.months', [1, 0, 0])
        ->click('Apply preset')
        ->assertSet('data.months', [2, 1, 0])
        ->assertAttributeContains($firstCell, 'class', 'fi-color-m2')
        ->assertEnabled($firstCell)
        ->click($firstCell)
        ->assertAttributeContains($firstCell, 'class', 'fi-color-m0')
        ->assertSet('data.months', [0, 1, 0])
        ->click('Save matrix')
        ->assertSet('saved.months', [0, 1, 0])
        ->click('Apply preset')
        ->assertSet('data.months', [2, 1, 0])
        ->click($firstCell)
        ->assertAttributeContains($firstCell, 'class', 'fi-color-m0')
        ->click('Save matrix')
        ->assertSet('saved.months', [0, 1, 0])
        ->assertNoJavaScriptErrors();
});
