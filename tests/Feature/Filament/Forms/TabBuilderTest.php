<?php

use App\Filament\Forms\Components\ColorSelect;
use App\Filament\Forms\Components\TabBuilder;
use App\Models\Color;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Group;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Fixtures\Livewire\TabBuilderFieldsForm;
use Tests\Fixtures\Livewire\TabBuilderForm;
use Tests\Fixtures\Models\TabBuilderDetail;
use Tests\Fixtures\Models\TabBuilderRecord;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Schema::create('tab_builder_records', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('owner_id')->nullable();
        $table->json('items')->nullable();
    });
    Schema::create('tab_builder_details', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('record_id')->nullable();
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->string('name')->nullable();
        $table->json('items')->nullable();
    });
});

it('hydrates defaults under the fixed keys and saves associative JSON', function (): void {
    $form = livewire(TabBuilderForm::class, ['defaults' => true]);

    $form->assertSet('data.items', ['plain' => ['note' => 'Default note']])
        ->call('save')->assertHasNoErrors()
        ->assertSet('saved.items', ['plain' => ['note' => 'Default note']]);
});

it('adds only available keys with their own schema and defaults', function (): void {
    $form = livewire(TabBuilderForm::class);
    $add = TestAction::make('add')->schemaComponent('items');

    $form->callAction($add, data: ['key' => 'other'])
        ->assertHasNoErrors()->assertSet('data.items.other', ['other_note' => 'Other default'])
        ->callAction($add, data: ['key' => 'plain'])
        ->assertSet('data.items.plain', ['note' => 'Default note']);

    $builder = $form->instance()->form->getComponent('items');
    expect($builder->getAvailableOptions())->not->toHaveKeys(['plain', 'other']);

    $form->callAction($add, data: ['key' => 'unknown'])->assertHasErrors();
});

it('prunes unknown and malformed items and uses different child schemas', function (): void {
    livewire(TabBuilderForm::class, ['initialState' => ['items' => [
        'plain' => ['note' => 'A'],
        'native' => ['native_note' => 'B'],
        'unknown' => ['note' => 'Discard'],
        'other' => 'invalid',
    ]]])->assertSet('data.items', ['plain' => ['note' => 'A'], 'native' => ['native_note' => 'B']])
        ->call('save')->assertHasNoErrors()
        ->assertSet('saved.items', ['plain' => ['note' => 'A'], 'native' => ['native_note' => 'B']]);
});

it('loads and updates relationship tabs while saving only JSON tabs in the field', function (): void {
    $record = TabBuilderRecord::create();
    $detail = $record->details()->create(['name' => 'Existing']);

    $form = livewire(TabBuilderForm::class, ['record' => $record, 'initialState' => ['items' => ['plain' => ['note' => 'JSON']]]])
        ->assertSet('data.items.profile.name', 'Existing')
        ->set('data.items.profile.name', 'Updated')
        ->call('save')->assertHasNoErrors()
        ->assertSet('saved.items', ['plain' => ['note' => 'JSON']]);

    expect($detail->fresh()->name)->toBe('Updated');
    expect($form->instance()->form->getComponent('items')->getItemState('profile'))->toBe(['name' => 'Updated']);
});

it('creates a related record without leaking its tab into JSON', function (): void {
    $record = TabBuilderRecord::create();

    livewire(TabBuilderForm::class, ['record' => $record, 'initialState' => ['items' => ['profile' => ['name' => 'New']]]])
        ->set('data.items.profile.name', 'New')
        ->call('save')->assertHasNoErrors()->assertSet('saved.items', []);

    expect($record->fresh()->details->name)->toBe('New');
});

it('validates every active tab before saving relationships', function (): void {
    $record = TabBuilderRecord::create();
    $detail = $record->details()->create(['name' => 'Original']);

    livewire(TabBuilderForm::class, ['record' => $record, 'initialState' => ['items' => [
        'plain' => ['note' => ''],
    ]]])->set('data.items.profile.name', 'Changed')->call('save')->assertHasErrors(['data.items.plain.note']);

    expect($detail->fresh()->name)->toBe('Original');
});

it('removes relationship records only when the form is saved', function (): void {
    $record = TabBuilderRecord::create();
    $detail = $record->details()->create(['name' => 'Remove']);
    $record->load('details');

    $form = livewire(TabBuilderForm::class, ['record' => $record, 'initialState' => []])
        ->callAction(TestAction::make('delete')->schemaComponent('items'), arguments: ['item' => 'profile'])
        ->assertSet('data.items', []);

    expect($detail->fresh())->not->toBeNull();
    $form->call('save')->assertHasNoErrors()->assertSet('data.items', []);
    expect($detail->fresh())->toBeNull();
});

it('supports relationship-only schemas', function (): void {
    $record = TabBuilderRecord::create();

    livewire(TabBuilderForm::class, ['record' => $record, 'relationshipOnly' => true, 'initialState' => ['items' => ['profile' => ['name' => 'Only relation']]]])
        ->set('data.items.profile.name', 'Only relation')
        ->call('save')->assertHasNoErrors()->assertSet('saved', []);

    expect($record->fresh()->details->name)->toBe('Only relation');
});

it('supports an additional relationship level through a group', function (): void {
    $record = TabBuilderRecord::create();
    $outer = $record->details()->create(['name' => 'Outer', 'items' => ['plain' => ['note' => 'JSON']]]);
    $inner = $outer->details()->create(['name' => 'Inner']);

    livewire(TabBuilderForm::class, ['record' => $record, 'nested' => true, 'initialState' => []])
        ->assertSet('data.details.items.profile.name', 'Inner')
        ->set('data.details.items.profile.name', 'Updated inner')
        ->set('data.details.items.plain.note', 'Updated JSON')
        ->call('save')->assertHasNoErrors()->assertSet('saved', []);

    expect($inner->fresh()->name)->toBe('Updated inner')
        ->and($outer->fresh()->items)->toBe(['plain' => ['note' => 'Updated JSON']]);
});

it('supports belongs-to relationship tabs', function (): void {
    $record = TabBuilderRecord::create();

    livewire(TabBuilderForm::class, ['record' => $record, 'relationshipName' => 'owner', 'initialState' => ['items' => ['profile' => ['name' => 'Owner']]]])
        ->set('data.items.profile.name', 'Owner')
        ->call('save')->assertHasNoErrors()->assertSet('saved.items', []);

    expect($record->fresh()->owner->name)->toBe('Owner');
});

it('rejects schema keys that cannot be used as fixed state paths', function (array $schemas): void {
    expect(fn () => TabBuilder::make('items')->schemas($schemas))->toThrow(InvalidArgumentException::class);
})->with([
    'numeric' => [[0 => []]],
    'empty' => [['' => []]],
    'nested' => [['profile.name' => []]],
]);

it('creates relationships after the parent record is created', function (): void {
    $form = livewire(TabBuilderForm::class)
        ->callAction(TestAction::make('add')->schemaComponent('items'), data: ['key' => 'profile'])
        ->set('data.items.profile.name', 'Created with parent')
        ->call('create')->assertHasNoErrors();

    expect($form->instance()->record->fresh()->details->name)->toBe('Created with parent')
        ->and($form->instance()->record->items)->toBe([]);
});

it('creates missing outer and inner relationships through a group', function (): void {
    $record = TabBuilderRecord::create();

    livewire(TabBuilderForm::class, ['record' => $record, 'nested' => true, 'initialState' => []])
        ->callAction(TestAction::make('add')->schemaComponent('details.items'), data: ['key' => 'profile'])
        ->set('data.details.items.profile.name', 'New inner')
        ->call('save')->assertHasNoErrors();

    expect($record->fresh()->details->details->name)->toBe('New inner');
});

it('clears the JSON field after its final tab is removed', function (): void {
    livewire(TabBuilderForm::class, ['initialState' => ['items' => ['plain' => ['note' => 'Remove']]]])
        ->callAction(TestAction::make('delete')->schemaComponent('items'), arguments: ['item' => 'plain'])
        ->call('save')->assertHasNoErrors()->assertSet('saved.items', []);
});

it('re-adds JSON tabs with fresh defaults and prevents duplicate additions', function (): void {
    $add = TestAction::make('add')->schemaComponent('items');
    $form = livewire(TabBuilderForm::class, ['initialState' => ['items' => ['plain' => ['note' => 'Changed']]]]);
    $form->callAction($add, data: ['key' => 'plain'])->assertHasErrors();
    $form->unmountAction();
    $form->callAction(TestAction::make('delete')->schemaComponent('items'), arguments: ['item' => 'plain'])
        ->callAction($add, data: ['key' => 'plain'])->assertHasNoErrors()
        ->assertSet('data.items.plain.note', 'Default note');
});

it('preserves relationships when the builder is disabled', function (): void {
    $record = TabBuilderRecord::create();
    $detail = $record->details()->create(['name' => 'Original']);

    livewire(TabBuilderForm::class, ['record' => $record, 'disabled' => true, 'initialState' => []])
        ->set('data.items', [])->call('save')->assertHasNoErrors()->assertSet('saved', []);

    expect($detail->fresh()->name)->toBe('Original');
});

it('dissociates a belongs-to relationship before deleting its tab record', function (): void {
    $detail = TabBuilderDetail::create(['name' => 'Owner']);
    $record = TabBuilderRecord::create(['owner_id' => $detail->id]);

    livewire(TabBuilderForm::class, ['record' => $record, 'relationshipName' => 'owner', 'initialState' => []])
        ->callAction(TestAction::make('delete')->schemaComponent('items'), arguments: ['item' => 'profile'])
        ->call('save')->assertHasNoErrors();

    expect($record->fresh()->owner_id)->toBeNull()
        ->and($detail->fresh())->toBeNull();
});

it('infers relationship names and preserves explicit keys for single fields', function (): void {
    createRequiredLanguages();
    $record = TabBuilderRecord::create();
    $record->details()->create(['name' => 'Existing detail']);

    $form = livewire(TabBuilderFieldsForm::class, ['record' => $record, 'initialState' => []]);
    $form->assertSet('data.items.details.name', 'Existing detail')
        ->callAction(TestAction::make('add')->schemaComponent('items'), data: ['key' => 'testguy'])
        ->assertSet('data.items.testguy.test', 'first')
        ->set('data.items.testguy.test', 'second')->call('save')->assertHasNoErrors()
        ->assertSet('saved.items', ['testguy' => ['test' => 'second']]);

    expect($form->instance()->form->getComponent('items')->getTabDefinitions())->toHaveKeys(['details', 'colors', 'spring', 'testguy']);
});

it('validates standalone fields inside their named tabs', function (): void {
    createRequiredLanguages();

    livewire(TabBuilderFieldsForm::class, ['initialState' => ['items' => ['testguy' => ['test' => null]]]])
        ->call('save')->assertHasErrors(['data.items.testguy.test']);
});

it('rejects duplicate inferred and explicit keys', function (): void {
    expect(fn () => TabBuilder::make('items')->schemas([
        Group::make()->relationship('details'),
        'details' => [],
    ]))->toThrow(InvalidArgumentException::class);

    expect(fn () => TabBuilder::make('items')->schemas([
        ColorSelect::make('default_colors')->pivotData(['tag' => 'default']),
        ColorSelect::make('spring_colors')->pivotData(['tag' => 'spring']),
    ]))->toThrow(InvalidArgumentException::class);
});

it('requires explicit keys for fields without relationships and validates inferred keys', function (): void {
    expect(fn () => TabBuilder::make('items')->schemas([Select::make('test')]))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => TabBuilder::make('items')->schemas([Group::make()->relationship('nested.details')]))
        ->toThrow(InvalidArgumentException::class);
});

it('exposes the implicit ColorSelect relationship through native metadata', function (): void {
    $field = ColorSelect::make('spring_colors')->pivotData(['tag' => 'spring'])->relationship();

    expect($field->hasRelationship())->toBeTrue()
        ->and($field->getRelationshipName())->toBe('colors')
        ->and($field->getRelationshipTitleAttribute())->toBe('name')
        ->and($field->getPivotData())->toBe(['tag' => 'spring']);
});

it('honors custom ColorSelect relationship names titles and query scopes', function (bool $single): void {
    createRequiredLanguages();
    $record = TabBuilderRecord::create();
    $red = Color::factory()->create(['name' => 'Red', 'hex_value' => '#ff0000']);
    $blue = Color::factory()->create(['name' => 'Blue', 'hex_value' => '#0000ff']);
    $green = Color::factory()->create(['name' => 'Green', 'hex_value' => '#00ff00']);
    $record->colors()->attach($red, ['tag' => 'default']);
    $record->colors()->attach($green, ['tag' => 'default']);
    $record->colors()->attach($red, ['tag' => 'spring']);
    $record->load('palette');

    $form = livewire(TabBuilderFieldsForm::class, [
        'record' => $record,
        'standalone' => true,
        'singleColor' => $single,
        'customColorRelationship' => true,
        'initialState' => [],
    ])->assertSet('data.colors', $single ? $red->id : [$red->id]);

    $field = $form->instance()->form->getComponentByStatePath('colors');
    expect($field->getRelationshipName())->toBe('palette')
        ->and($field->getRelationshipTitleAttribute())->toBe('hex_value')
        ->and($field->getOptions())->toMatchArray([$red->id => '#ff0000', $blue->id => '#0000ff'])
        ->and($field->getOptions())->not->toHaveKey($green->id)
        ->and($field->getSearchResults('#ff'))->toBe([$red->id => '#ff0000'])
        ->and($field->getCreateOptionUsing())->toBeNull();

    if ($single) {
        expect($field->getOptionLabel())->toBe('#ff0000');
    } else {
        expect($field->getOptionLabels())->toBe([$red->id => '#ff0000']);
    }

    $form->set('data.colors', $single ? $blue->id : [$blue->id])
        ->call('save')->assertHasNoErrors()->assertSet('saved', []);

    expect($record->colors()->wherePivot('tag', 'default')->orderBy('colors.id')->pluck('colors.id')->all())
        ->toBe([$blue->id, $green->id]);
    expect($record->colors()->wherePivot('tag', 'spring')->pluck('colors.id')->all())->toBe([$red->id]);
})->with([true, false]);

it('can switch between custom relationship options and the localized color catalog', function (): void {
    createRequiredLanguages();
    $record = TabBuilderRecord::create();
    $red = Color::factory()->create(['name' => 'Red', 'hex_value' => '#ff0000']);
    $green = Color::factory()->create(['name' => 'Green', 'hex_value' => '#00ff00']);
    $record->colors()->attach($red, ['tag' => 'default']);

    $form = livewire(TabBuilderFieldsForm::class, [
        'record' => $record, 'standalone' => true, 'customColorRelationship' => true, 'initialState' => [],
    ]);
    $field = $form->instance()->form->getComponentByStatePath('colors');

    expect($field->getOptionLabels())->toBe([$red->id => '#ff0000']);
    $field->relationship();
    expect($field->getOptions())->toMatchArray([$red->id => 'Red', $green->id => 'Green'])
        ->and($field->getOptionLabels())->toBe([$red->id => 'Red'])
        ->and($field->getSearchResults('Green'))->toBe([$green->id => 'Green']);

    $field->relationship('palette', 'hex_value');
    expect($field->getOptions())->toMatchArray([$red->id => '#ff0000', $green->id => '#00ff00'])
        ->and($field->getOptionLabels())->toBe([$red->id => '#ff0000']);
});

it('loads and saves color tabs in the pivot while keeping them out of JSON', function (): void {
    createRequiredLanguages();
    $record = TabBuilderRecord::create();
    $red = Color::factory()->create(['name' => 'Red']);
    $blue = Color::factory()->create(['name' => 'Blue']);
    $record->colors()->attach($red, ['tag' => 'default']);
    $record->colors()->attach($red, ['tag' => 'spring']);
    $record->load('colors');

    livewire(TabBuilderFieldsForm::class, ['record' => $record, 'initialState' => ['items' => ['testguy' => ['test' => 'first']]]])
        ->assertSet('data.items.colors.colors', [$red->id])
        ->assertSet('data.items.spring.spring_colors', [$red->id])
        ->set('data.items.colors.colors', [$blue->id])
        ->call('save')->assertHasNoErrors()->assertSet('saved.items', ['testguy' => ['test' => 'first']]);

    expect($record->colors()->wherePivot('tag', 'default')->pluck('colors.id')->all())->toBe([$blue->id])
        ->and($record->colors()->wherePivot('tag', 'spring')->pluck('colors.id')->all())->toBe([$red->id]);
});

it('detaches only the removed color tab pivot scope and keeps color records', function (): void {
    createRequiredLanguages();
    $record = TabBuilderRecord::create();
    $red = Color::factory()->create(['name' => 'Red']);
    $record->colors()->attach($red, ['tag' => 'default']);
    $record->colors()->attach($red, ['tag' => 'spring']);

    $form = livewire(TabBuilderFieldsForm::class, ['record' => $record, 'initialState' => []])
        ->callAction(TestAction::make('delete')->schemaComponent('items'), arguments: ['item' => 'colors']);

    expect($record->colors()->wherePivot('tag', 'default')->exists())->toBeTrue();
    $form->call('save')->assertHasNoErrors();

    expect($record->colors()->wherePivot('tag', 'default')->exists())->toBeFalse()
        ->and($record->colors()->wherePivot('tag', 'spring')->exists())->toBeTrue()
        ->and($red->fresh())->not->toBeNull();
    expect($form->get('data.items'))->not->toHaveKey('colors');
});

it('adds and saves an inferred color tab after the parent is created', function (bool $useClosure): void {
    createRequiredLanguages();
    $red = Color::factory()->create(['name' => 'Red']);

    $form = livewire(TabBuilderFieldsForm::class, ['relationshipOnly' => true, 'usePivotDataClosure' => $useClosure])
        ->callAction(TestAction::make('add')->schemaComponent('items'), data: ['key' => 'colors'])
        ->set('data.items.colors.colors', [$red->id])->call('create')->assertHasNoErrors()->assertSet('saved', []);

    expect($form->instance()->record->colors()->wherePivot('tag', 'default')->pluck('colors.id')->all())->toBe([$red->id]);
})->with([true, false]);

it('supports native Select relationship tabs', function (): void {
    createRequiredLanguages();
    $record = TabBuilderRecord::create();
    $red = Color::factory()->create(['name' => 'Red']);
    $blue = Color::factory()->create(['name' => 'Blue']);
    $record->colors()->attach($red);

    livewire(TabBuilderFieldsForm::class, ['record' => $record, 'nativeColors' => true, 'relationshipOnly' => true, 'initialState' => []])
        ->assertSet('data.items.native_colors.selected_colors', [(string) $red->id])
        ->set('data.items.native_colors.selected_colors', [$blue->id])
        ->call('save')->assertHasNoErrors()->assertSet('saved', []);

    expect($record->colors()->pluck('colors.id')->all())->toBe([$blue->id]);
});

it('preserves unsaved color selections when another tab is added', function (): void {
    createRequiredLanguages();
    $record = TabBuilderRecord::create();
    $red = Color::factory()->create(['name' => 'Red']);
    $blue = Color::factory()->create(['name' => 'Blue']);
    $record->colors()->attach($red, ['tag' => 'default']);

    livewire(TabBuilderFieldsForm::class, ['record' => $record, 'initialState' => []])
        ->set('data.items.colors.colors', [$blue->id])
        ->callAction(TestAction::make('add')->schemaComponent('items'), data: ['key' => 'testguy'])
        ->assertSet('data.items.colors.colors', [$blue->id]);
});

it('supports ColorSelect outside TabBuilder in single and multiple modes', function (bool $single): void {
    createRequiredLanguages();
    $record = TabBuilderRecord::create();
    $red = Color::factory()->create(['name' => 'Red']);
    $blue = Color::factory()->create(['name' => 'Blue']);
    $record->colors()->attach($red, ['tag' => 'default']);
    $record->colors()->attach($red, ['tag' => 'spring']);
    $record->load('colors');

    livewire(TabBuilderFieldsForm::class, ['record' => $record, 'standalone' => true, 'singleColor' => $single, 'initialState' => []])
        ->assertSet('data.colors', $single ? $red->id : [$red->id])
        ->set('data.colors', $single ? $blue->id : [$blue->id])->call('save')->assertHasNoErrors()
        ->assertSet('saved', []);

    expect($record->colors()->wherePivot('tag', 'default')->pluck('colors.id')->all())->toBe([$blue->id])
        ->and($record->colors()->wherePivot('tag', 'spring')->pluck('colors.id')->all())->toBe([$red->id]);
})->with([true, false]);

it('resolves color field relationships from the enclosing relationship group', function (): void {
    createRequiredLanguages();
    $record = TabBuilderRecord::create();
    $outer = $record->details()->create(['name' => 'Outer']);
    $red = Color::factory()->create(['name' => 'Red']);
    $blue = Color::factory()->create(['name' => 'Blue']);
    $outer->colors()->attach($red, ['tag' => 'default']);

    livewire(TabBuilderFieldsForm::class, ['record' => $record, 'nested' => true, 'initialState' => []])
        ->assertSet('data.details.items.colors.colors', [$red->id])
        ->set('data.details.items.colors.colors', [$blue->id])->call('save')->assertHasNoErrors();

    expect($outer->colors()->wherePivot('tag', 'default')->pluck('colors.id')->all())->toBe([$blue->id])
        ->and($record->colors()->exists())->toBeFalse();
});

it('scopes color loading saving and clearing by every pivot data column', function (bool $single, bool $useClosure): void {
    createRequiredLanguages();
    Schema::table('colorables', function (Blueprint $table): void {
        $table->string('palette')->nullable();
    });

    $pivotData = ['tag' => 'default', 'palette' => 'primary'];
    $record = TabBuilderRecord::create(['items' => ['pivot_data' => $pivotData]]);
    $red = Color::factory()->create(['name' => 'Red']);
    $blue = Color::factory()->create(['name' => 'Blue']);
    $record->scopedColors()->attach($red, $pivotData);
    $record->scopedColors()->attach($red, ['tag' => 'default', 'palette' => 'secondary']);
    $record->scopedColors()->attach($red, ['tag' => 'spring', 'palette' => 'primary']);
    $record->load('scopedColors');

    $form = livewire(TabBuilderFieldsForm::class, [
        'record' => $record,
        'standalone' => true,
        'singleColor' => $single,
        'colorRelationshipName' => 'scopedColors',
        'colorPivotData' => $useClosure ? [] : $pivotData,
        'usePivotDataClosure' => $useClosure,
        'initialState' => [],
    ])->assertSet('data.colors', $single ? $red->id : [$red->id])
        ->set('data.colors', $single ? $blue->id : [$blue->id])
        ->call('save')->assertHasNoErrors()->assertSet('saved', []);

    expect($record->scopedColors()->wherePivot('tag', 'default')->wherePivot('palette', 'primary')->pluck('colors.id')->all())
        ->toBe([$blue->id]);
    expect($record->scopedColors()->wherePivot('tag', 'default')->wherePivot('palette', 'secondary')->pluck('colors.id')->all())
        ->toBe([$red->id]);

    $form->set('data.colors', $single ? null : [])->call('save')->assertHasNoErrors();

    expect($record->scopedColors()->wherePivot('tag', 'default')->wherePivot('palette', 'primary')->exists())->toBeFalse()
        ->and($record->scopedColors()->wherePivot('palette', 'secondary')->pluck('colors.id')->all())->toBe([$red->id])
        ->and($record->scopedColors()->wherePivot('tag', 'spring')->pluck('colors.id')->all())->toBe([$red->id]);
})->with([true, false])->with([true, false]);

it('supports null pivot data values without touching other pivot scopes', function (): void {
    createRequiredLanguages();
    $record = TabBuilderRecord::create();
    $red = Color::factory()->create(['name' => 'Red']);
    $blue = Color::factory()->create(['name' => 'Blue']);
    $record->colors()->attach($red, ['tag' => null]);
    $record->colors()->attach($red, ['tag' => 'spring']);

    livewire(TabBuilderFieldsForm::class, [
        'record' => $record, 'standalone' => true, 'colorPivotData' => ['tag' => null], 'initialState' => [],
    ])->assertSet('data.colors', [$red->id])
        ->set('data.colors', [$blue->id])->call('save')->assertHasNoErrors();

    expect($record->colors()->wherePivotNull('tag')->pluck('colors.id')->all())->toBe([$blue->id])
        ->and($record->colors()->wherePivot('tag', 'spring')->pluck('colors.id')->all())->toBe([$red->id]);
});
