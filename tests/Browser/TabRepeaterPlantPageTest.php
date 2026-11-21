<?php

use App\Filament\Resources\Plants\PlantResource;
use App\Models\Cultivar;
use App\Models\Plant;
use App\Models\PlantMaintenance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('keeps the plant edit page in place when switching and deleting maintenance tabs', function (): void {
    createRequiredLanguages();
    actingAs(User::factory()->admin()->create());
    $plant = Plant::factory()->create();
    $cultivar = Cultivar::factory()->create(['plant_id' => $plant->id, 'name' => null]);

    foreach (['default', 'shrub', 'tree'] as $group) {
        PlantMaintenance::factory()->create([
            'cultivar_id' => $cultivar->id,
            'group' => $group,
            'watering_needs' => null,
            'difficulty' => null,
        ]);
    }

    $page = visit(PlantResource::getUrl('edit', ['record' => $plant], panel: 'admin'));
    $page->resize(1280, 720);
    $page->script("document.getElementById('form.baseCultivar.cultivars-tabs').scrollIntoView({ block: 'center' })");
    $page->assertPresent('[id="form.baseCultivar.cultivars-tabs"] [role="tab"]');
    $page->click('[id="form.baseCultivar.cultivars-tabs"] [role="tab"]:nth-of-type(5)')
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
        document.querySelector('[x-ref="tabsRoot"] [role="tabpanel"].fi-active .fi-fo-repeater-item-header-end-actions li:last-child button').click()
        JS);
    $page->assertCount('[x-ref="tabsRoot"] [role="tab"]', 2)
        ->assertAttribute('[x-ref="tabsRoot"] [role="tab"]:first-of-type', 'aria-selected', 'true');
    $afterDelete = $page->script(<<<'JS'
        async () => {
            await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(() => requestAnimationFrame(resolve))));
            const root = document.querySelector('[x-ref="tabsRoot"]');
            return { scrollY: window.scrollY, minHeight: root.style.minHeight, display: getComputedStyle(root).display };
        }
        JS);

    expect($afterDelete['scrollY'])->toEqualWithDelta($before, 1)
        ->and($afterDelete['minHeight'])->toBe('')
        ->and($afterDelete['display'])->toBe('flex');
    $page->assertNoJavaScriptErrors();
});
