<?php

use App\Enums\HardinessZone;
use App\Support\ColorTag;

it('creates stable color tag names and CSS classes', function (string $colorName, string $name): void {
    expect(ColorTag::name($colorName))->toBe($name)
        ->and(ColorTag::cssClass($colorName))->toBe("fi-color-{$name}");
})->with([
    'color name' => ['Red', 'custom-red'],
    'multiple words' => ['Light Blue', 'custom-light-blue'],
    'whitespace and case' => ['  DARK Green  ', 'custom-dark-green'],
]);

it('defines every supported hardiness zone and rejects out-of-range values', function (): void {
    expect(array_map(fn (HardinessZone $zone): int => $zone->value, HardinessZone::cases()))
        ->toBe(range(1, 13))
        ->and(HardinessZone::tryFrom(0))->toBeNull()
        ->and(HardinessZone::tryFrom(14))->toBeNull();
});
