<?php

use Tests\Fixtures\Livewire\MatrixFieldForm;

use function Pest\Livewire\livewire;

it('rejects tampered matrix values', function (array $values): void {
    livewire(MatrixFieldForm::class)
        ->set('data.months', $values)
        ->call('save')
        ->assertHasErrors(['data.months']);
})->with([
    'out of range' => [[0, 3, 0]],
    'negative' => [[0, -1, 0]],
    'nested array' => [[0, [1], 0]],
    'arbitrary text' => [[0, 'bad', 0]],
    'too many cells' => [[0, 1, 2, 0]],
    'missing cells' => [[0, 1]],
]);

it('saves valid matrix values', function (): void {
    livewire(MatrixFieldForm::class)
        ->set('data.months', [0, 1, 2])
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('saved.months', [0, 1, 2]);
});
