<?php

use App\Enums\Month;
use Illuminate\Support\Facades\App;

afterEach(function (): void {
    App::setLocale('en');
});

it('maps every month to a localized option', function (): void {
    App::setLocale('en');

    expect(Month::options())->toMatchArray([
        1 => 'January',
        6 => 'June',
        12 => 'December',
    ])
        ->and(array_keys(Month::options()))->toBe(range(1, 12));
});

it('keeps English and current-locale month names searchable', function (): void {
    App::setLocale('de');

    expect(Month::JANUARY->englishName())->toBe('January')
        ->and(Month::searchableMap()[1])->toContain('january', 'januar');
});