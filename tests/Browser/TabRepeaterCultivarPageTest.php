<?php

use App\Filament\Resources\Cultivars\CultivarResource;
use App\Models\Cultivar;
use App\Models\Plant;
use App\Models\PlantMaintenance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('keeps the cultivar edit page in place when switching and deleting maintenance tabs', function (): void {
    createRequiredLanguages();
    actingAs(User::factory()->admin()->create());
    $plant = Plant::factory()->create();
    $cultivar = Cultivar::factory()->create(['plant_id' => $plant->id, 'name' => 'Scroll cultivar']);

    foreach (['default', 'shrub', 'tree'] as $group) {
        PlantMaintenance::factory()->create([
            'cultivar_id' => $cultivar->id,
            'group' => $group,
            'watering_needs' => null,
            'difficulty' => null,
        ]);
    }

    $page = visit(CultivarResource::getUrl('edit', ['record' => $cultivar], panel: 'admin'));
    $page->resize(1280, 720);
    $page->script("document.getElementById('form.cultivars-tabs').scrollIntoView({ block: 'center' })");
    $page->assertPresent('[id="form.cultivars-tabs"] [role="tab"]');
    $page->click('[id="form.cultivars-tabs"] [role="tab"]:nth-of-type(5)')
        ->assertCount('[x-ref="tabsRoot"] [role="tab"]', 3);

    $before = $page->script(<<<'JS'
        () => {
            window.scrollTo(0, document.documentElement.scrollHeight);
            const before = window.scrollY;
            document.querySelectorAll('[x-ref="tabsRoot"] [role="tab"]')[1].click();
            return before;
        }
        JS);
    $page->assertAttribute('[x-ref="tabsRoot"] [role="tab"]:nth-of-type(2)', 'aria-selected', 'true');
    $afterSwitch = $page->script(<<<'JS'
        async () => {
            await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(() => requestAnimationFrame(resolve))));
            return window.scrollY;
        }
        JS);

    expect($before)->toBeGreaterThan(0)
        ->and($afterSwitch)->toEqualWithDelta($before, 1);

    $page->script(<<<'JS'
        () => {
            const root = document.querySelector('[x-ref="tabsRoot"]');
            window.scrollTo(0, window.scrollY + root.getBoundingClientRect().top - 100);

            // Start at pointerdown so Playwright's positioning of the button
            // is excluded, and record every frame rather than only the result.
            root.addEventListener('pointerdown', () => {
                window.deletionScroll = { before: window.scrollY, samples: [], recording: true };
                const sample = () => {
                    const trace = window.deletionScroll;
                    if (! trace.recording) return;
                    trace.samples.push(window.scrollY);
                    requestAnimationFrame(sample);
                };
                sample();
            }, { once: true });
        }
        JS);
    $page->click('[x-ref="tabsRoot"] [role="tabpanel"].fi-active .fi-fo-repeater-item-header-end-actions li:last-child button');
    $page->assertCount('[x-ref="tabsRoot"] [role="tab"]', 2)
        ->assertAttribute('[x-ref="tabsRoot"] [role="tab"]:first-of-type', 'aria-selected', 'true')
        ->assertScript('document.querySelector("[x-ref=tabsRoot]").style.minHeight === ""');
    $afterDelete = $page->script(<<<'JS'
        async () => {
            await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
            const trace = window.deletionScroll;
            trace.samples.push(window.scrollY);
            trace.recording = false;
            return trace;
        }
        JS);

    expect($afterDelete['before'])->toBeGreaterThan(0)
        ->and($afterDelete['samples'])->not->toBeEmpty();
    foreach ($afterDelete['samples'] as $scrollY) {
        expect($scrollY)->toEqualWithDelta($afterDelete['before'], 1);
    }
    $page->assertNoJavaScriptErrors();
});
