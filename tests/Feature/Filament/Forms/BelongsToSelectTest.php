<?php

use App\Filament\Forms\Components\BelongsToSelect;
use App\Filament\Resources\Cultivars\Pages\EditCultivar;
use App\Filament\Resources\Genera\Pages\EditGenus;
use App\Filament\Resources\Plants\Pages\CreatePlant;
use App\Filament\Resources\Plants\Pages\EditPlant;
use App\Filament\Resources\Species\SpeciesResource;
use App\Models\Cultivar;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Language;
use App\Models\Localizable;
use App\Models\Plant;
use App\Models\Species;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->admin()->create());
    createRequiredLanguages();
    app()->setLocale('en');
    Filament::setCurrentPanel('admin');
    Filament::bootCurrentPanel();
});

it('infers a relationship from the field name and keeps localized and parent search', function (): void {
    $genus = Genus::factory()->create(['name' => 'Prunus']);
    $genus->replaceNameTranslations(['en' => 'Cherry genus']);
    $species = Species::factory()->create(['genus_id' => $genus->id, 'name' => 'serrulata']);
    $species->replaceNameTranslations(['en' => 'Japanese cherry']);
    $excluded = Species::factory()->create(['genus_id' => $genus->id, 'name' => 'avium']);

    $page = livewire(CreatePlant::class);
    $field = $page->instance()->form->getComponent('species_id');

    expect($field->getRelationshipName())->toBe('species')
        ->and($field->getSearchResults('Japanese cherry'))->toHaveKeys([$species->id])
        ->and($field->getSearchResults('Cherry genus'))->toHaveKeys([$species->id, $excluded->id])
        ->and($field->getSearchResults(''))->toHaveKeys([$species->id, $excluded->id]);

    $field->relationship(modifyQueryUsing: fn (Builder $query): Builder => $query->where('_localizables.value', 'Japanese cherry'))
        ->preload()
        ->state($species->id);

    expect($field->getRelationshipName())->toBe('species')
        ->and(array_keys($field->getSearchResults('Cherry genus')))->toBe([$species->id])
        ->and($field->getOptions())->toBe([$species->id => $field->getOptionLabel()])
        ->and($field->getSelectedRecord()->is($species))->toBeTrue();

    $field->parentSearchable(false);
    expect($field->getSearchResults('Cherry genus'))->toBe([]);

    $field->state($excluded->id);
    expect($field->getSelectedRecord())->toBeNull();
});

it('uses an explicit relationship and custom title column in search, preload and selected labels', function (): void {
    $english = Language::whereCode('en')->firstOrFail();
    $dutch = Language::whereCode('nl')->firstOrFail();
    $searches = [];
    $field = BelongsToSelect::make('locale')->relationship(
        name: fn (): string => 'language',
        titleAttribute: fn (): string => 'code',
        modifyQueryUsing: function (Builder $query, ?string $search) use ($dutch, &$searches): void {
            $searches[] = $search;
            $query->whereKeyNot($dutch->id);
        },
    )->preload();

    $page = livewire(CreatePlant::class);
    Schema::make($page->instance())->model(Localizable::class)->statePath('data')->components([$field])->getComponents();
    $field->state($english->id);

    expect($field->getOptions())->toBe([$english->id => 'en'])
        ->and($field->getSearchResults('en'))->toBe([$english->id => 'en'])
        ->and($field->getOptionLabel())->toBe('en')
        ->and($searches)->toContain('en', null);
});

it('resolves the edit hint using the related model when a relationship has an alias', function (): void {
    $species = Species::factory()->create();
    $field = BelongsToSelect::make('taxon')->relationship('parent');
    $page = livewire(CreatePlant::class);
    Schema::make($page->instance())->model(Plant::class)->statePath('data')->components([$field])->getComponents();
    $field->state($species->id);

    expect((string) $field->getHint())->toContain(
        SpeciesResource::getUrl('edit', ['record' => $species], panel: 'admin'),
    );
});

it('reports a missing relationship when a field without an inferred name renders or loads data', function (): void {
    $field = BelongsToSelect::make('taxon');

    expect($field->hasRelationship())->toBeFalse()
        ->and(fn () => $field->getSearchResults('test'))
        ->toThrow(LogicException::class, 'BelongsToSelect [taxon] requires a relationship.')
        ->and(fn () => $field->toEmbeddedHtml())
        ->toThrow(LogicException::class, 'BelongsToSelect [taxon] requires a relationship.');
});

it('rejects an invalid relationship type with a configuration error', function (): void {
    $field = BelongsToSelect::make('plants_id');
    Schema::make()->model(Species::class)->components([$field])->getComponents();

    expect(fn () => $field->getRelationship())
        ->toThrow(LogicException::class, 'must be a BelongsTo relationship.');
});

it('can exclude the current record from a self-referencing relationship', function (): void {
    $family = Family::factory()->create(['name' => 'Self family']);
    $other = Family::factory()->create(['name' => 'Other family']);
    $model = new class extends Model
    {
        protected $table = 'families';

        public function parent(): BelongsTo
        {
            return $this->belongsTo(self::class, 'parent_id');
        }
    };
    $field = BelongsToSelect::make('parent_id')->relationship(ignoreRecord: true)->preload();
    $page = livewire(CreatePlant::class);
    Schema::make($page->instance())
        ->model($model->newFromBuilder($family->getAttributes()))
        ->statePath('data')
        ->components([$field])
        ->getComponents();

    expect($field->getOptions())->toBe([$other->id => 'Other family'])
        ->and($field->getSearchResults('family'))->toBe([$other->id => 'Other family']);

    $field->state($family->id);
    expect($field->getSelectedRecord())->toBeNull();
});

it('creates and edits a related species through the inferred select’s option actions', function (): void {
    $genus = Genus::factory()->create();
    $english = Language::whereCode('en')->firstOrFail();
    $page = livewire(CreatePlant::class)
        ->callAction(TestAction::make('createOption')->schemaComponent('species_id'), data: [
            'genus_id' => $genus->id,
            'name' => 'serrulata',
            'localizations' => [['language_id' => $english->id, 'value' => 'Japanese cherry']],
        ])
        ->assertHasNoActionErrors();

    $species = $genus->species()->where('name', 'serrulata')->firstOrFail();

    $page->assertSchemaStateSet(['species_id' => (string) $species->id], 'form')
        ->mountAction(TestAction::make('editOption')->schemaComponent('species_id'))
        ->assertActionDataSet(['name' => 'serrulata'])
        ->setActionData(['name' => 'avium'])
        ->callMountedAction()
        ->assertHasNoActionErrors();

    expect($species->fresh()->name)->toBe('avium')
        ->and($species->getTranslation('en'))->toBe('Japanese cherry');
});

it('loads, changes and clears the inferred foreign key on the edit page', function (): void {
    $previousSpecies = Species::factory()->create();
    $newSpecies = Species::factory()->create();
    $plant = Plant::factory()->for($previousSpecies)->create();

    livewire(EditPlant::class, ['record' => $plant->id])
        ->assertSchemaStateSet(['species_id' => (string) $previousSpecies->id], 'form')
        ->set('data.species_id', (string) $newSpecies->id)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($plant->fresh()->species_id)->toBe($newSpecies->id);

    livewire(EditPlant::class, ['record' => $plant->id])
        ->set('data.species_id', null)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($plant->fresh()->species_id)->toBeNull();
});

it('searches a top-level select without loading the rest of the edit form', function (string $pageClass, string $recordClass, string $relatedClass, string $relationship): void {
    $related = $relatedClass::factory()->create(['name' => 'searchable']);
    $record = $recordClass::factory()->for($related, $relationship)->create();
    if ($record instanceof Plant) {
        $record->baseCultivar()->create();
    }
    $page = livewire($pageClass, ['record' => $record->id]);

    DB::enableQueryLog();
    try {
        $page->call('callSchemaComponentMethod', "form.{$relationship}_id", 'getSearchResultsForJs', ['search' => 'searchable']);
        $queries = collect(DB::getQueryLog())->pluck('query')->map(fn (string $query) => str_replace(['`', '"'], '', $query));
    } finally {
        DB::disableQueryLog();
        DB::flushQueryLog();
    }

    expect(array_column($page->effects['returns'][0], 'value'))->toBe([(string) $related->id])
        ->and($page->effects)->not->toHaveKey('html')
        ->and($page->effects['partials'] ?? [])->toBeEmpty()
        ->and($queries->filter(fn (string $query) => str_contains($query, " from {$related->getTable()} "))->count())->toBe(1)
        ->and($queries->contains(fn (string $query) => str_contains($query, ' from plant_properties ')))->toBeFalse();
})->with([
    'plant species' => [EditPlant::class, Plant::class, Species::class, 'species'],
    'genus family' => [EditGenus::class, Genus::class, Family::class, 'family'],
    'cultivar plant' => [EditCultivar::class, Cultivar::class, Plant::class, 'plant'],
]);

it('loads the current species label without the localization search joins', function (): void {
    $genus = Genus::factory()->create(['name' => 'Prunus']);
    $species = Species::factory()->create(['genus_id' => $genus->id, 'name' => 'serrulata']);
    $page = livewire(CreatePlant::class);
    $field = $page->instance()->form->getComponent('species_id')->state($species->id);

    DB::enableQueryLog();
    try {
        $label = $field->getOptionLabel();
        $queries = collect(DB::getQueryLog())->pluck('query');
    } finally {
        DB::disableQueryLog();
        DB::flushQueryLog();
    }

    expect($label)->toContain('serrulata', 'Prunus')
        ->and($queries->contains(fn (string $query) => str_contains($query, ' join ')))->toBeFalse();
});
