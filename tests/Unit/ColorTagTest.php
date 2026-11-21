<?php

use App\Support\ColorTag;

it('generates consistent Filament color tag names and CSS classes', function () {
    expect(ColorTag::name('Red'))->toBe('custom-red')
        ->and(ColorTag::cssClass('Red'))->toBe('fi-color-custom-red');
});
