<?php

use Pest\Browser\Api\Livewire;
use Tests\Fixtures\Livewire\TabRepeaterForm;

it('preserves scroll when deleting the selected item', function (bool $partial): void {
    $page = Livewire::test(TabRepeaterForm::class, ['partial' => $partial]);
    $page->resize(1280, 720)
        ->click('[role="tab"]:nth-of-type(2)')
        ->assertAttribute('[role="tab"]:nth-of-type(2)', 'aria-selected', 'true');

    $before = $page->script(<<<'JS'
        async () => {
            await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
            window.scrollTo(0, document.documentElement.scrollHeight);
            const before = window.scrollY;
            document.querySelector('[role="tabpanel"].fi-active [data-test="delete-item"]').click();
            return before;
        }
        JS);

    $page->assertAttribute('[role="tab"]:first-of-type', 'aria-selected', 'true');
    $after = $page->script(<<<'JS'
        async () => {
            await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(() => requestAnimationFrame(resolve))));
            return { scrollY: window.scrollY, minHeight: document.querySelector('[x-ref="tabsRoot"]').style.minHeight };
        }
        JS);

    expect($before)->toBeGreaterThan(500)
        ->and($after['scrollY'])->toEqualWithDelta($before, 1)
        ->and($after['minHeight'])->toBe('');
    $page->assertNoJavaScriptErrors();
})->with(['partial render' => true, 'full render' => false]);

it('lets shorter panels and the empty state shrink naturally', function (): void {
    $page = Livewire::test(TabRepeaterForm::class);
    $page->resize(1280, 720)->assertVisible('[x-ref="tabsRoot"]');
    $tallHeight = $page->script('document.querySelector("[x-ref=tabsRoot]").getBoundingClientRect().height');

    $page->click('[role="tab"]:nth-of-type(3)')
        ->assertAttribute('[role="tab"]:nth-of-type(3)', 'aria-selected', 'true')
        ->assertScript('document.querySelector("[x-ref=tabsRoot]").style.minHeight === ""');
    $short = $page->script(<<<'JS'
        async () => {
            await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(() => requestAnimationFrame(resolve))));
            const root = document.querySelector('[x-ref="tabsRoot"]');
            return { height: root.getBoundingClientRect().height, minHeight: root.style.minHeight };
        }
        JS);

    expect($short['height'])->toBeLessThan($tallHeight - 400)
        ->and($short['minHeight'])->toBe('');

    foreach ([2, 1, 0] as $remaining) {
        $page->script("document.querySelector('[role=tabpanel].fi-active [data-test=delete-item]').click()");
        $page->assertCount('[role="tab"]', $remaining);
    }

    $page->assertSee(__('No Content'))
        ->assertNoJavaScriptErrors();
    $empty = $page->script(<<<'JS'
        async () => {
            await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(() => requestAnimationFrame(resolve))));
            const root = document.querySelector('[x-ref="tabsRoot"]');
            return { height: root.getBoundingClientRect().height, minHeight: root.style.minHeight };
        }
        JS);

    expect($empty['height'])->toBeLessThan($short['height'])
        ->and($empty['minHeight'])->toBe('');
});
