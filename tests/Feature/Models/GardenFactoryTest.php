<?php

use App\Models\Garden;

it('creates a valid garden with an owner and winter hardiness', function (): void {
    $garden = Garden::factory()->create();

    expect($garden->user)->not->toBeNull()
        ->and($garden->winter_hardiness)->toBeInt()->toBeBetween(1, 13);
});