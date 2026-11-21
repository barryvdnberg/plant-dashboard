# TabBuilder

`TabBuilder` gives you a fixed set of tabs. Users can add each key once and remove it again. Each tab owns its own fields. There are no UUIDs, editable keys, cloning, or reordering.

```php
use App\Filament\Forms\Components\TabBuilder;
use App\Filament\Forms\Components\TabBuilder\TabSchema;
use Filament\Forms\Components\TextInput;

TabBuilder::make('content')
    ->schemas([
        'summary' => TabSchema::make('Summary')->schema([
            TextInput::make('title')->required(),
        ]),
        'seo' => TabSchema::make('Search settings')
            ->relationship('metadata')
            ->schema([
                TextInput::make('description'),
            ]),
    ])
    ->default(['summary' => []]);

```

Import `App\Filament\Forms\Components\TabBuilder\TabSchema` for named definitions and relationships. This is a layout component that uses Filament's singular relationship handling. It is not `Filament\Schemas\Schema` (the form container).

`schemas()` also accepts plain arrays, normal Filament schema containers, layout components like `Group`, and closures returning a definition. Keys must start with a letter or underscore. After that, use only letters, digits, underscores, and hyphens. Keys, definition names or labels, and relationship names stay independent.

You can pass single fields directly. If a component declares a relationship, it can skip its array key. Its relationship name becomes the tab key. Ordinary fields still need explicit keys. Multiple tabs sharing a relationship name also need explicit keys:

```php
use App\Filament\Forms\Components\ColorSelect;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Group;

TabBuilder::make('content')->schemas([
    Group::make(FoliageForm::form())->relationship('foliage'),
    Group::make(PropertiesForm::form())->relationship('properties'),
    'testkey' => Select::make('test')->options(['first' => 'First', 'second' => 'Second']),
    ColorSelect::make('colors')->pivotData(['tag' => 'default']),
    'spring' => ColorSelect::make('spring_colors')->pivotData(['tag' => 'spring'])->label('Spring colors'),
]);

```

Here `testkey` writes to `content.testkey.test`. Relationship fields like `ColorSelect` and Filament's relationship `Select` drop out of the JSON payload, just like relationship groups. If every definition in the builder uses a relationship, `content` drops out too.

`ColorSelect` uses `colors` as its default relationship, no matter what you name the field. `getRelationshipName()` returns that name. Filament's inherited `getPivotData()` returns your configured pivot values. Use `pivotData(['tag' => 'default'])` to scope loading, saving, and tab removal to those values. Every pivot column you configure takes part in the scope. `ColorSelect` does not hardcode any column. An empty array leaves the relationship unscoped. A `null` value matches rows where that column is `null`. Filament's inherited `pivotData()` supports both arrays and closures. Localized choices stay available across pivot scopes. Selected IDs never get written into parent attributes, whether in single or multiple mode.

Call `relationship()` again to customize how colors are resolved. It defaults to `name: 'colors'` and `titleAttribute: 'name'`. `ColorSelect` only registers the loading, saving, option, search, and label behavior it needs. It skips the parent `relationship()` setup and does not attach create or edit-option handlers. Custom setups use Filament's relationship option and search queries plus selected labels. They respect your title attribute and query callback. The default setup uses the localized color catalog. Tagged pivot scoping and skipping parent attributes keep working either way:

```php
ColorSelect::make('selected_colors')
    ->relationship('palette', 'hex_value', fn ($query) => $query->where('colors.name', '!=', 'Green'))
    ->pivotData(['tag' => 'default']);

```

Tabs without a relationship save as an associative array, such as `['summary' => ['title' => 'Example']]`. Cast `content` to `array` on the parent model. Relationship tabs leave that array and use Filament's normal `HasOne`, `MorphOne`, or `BelongsTo` loading and saving. When every tab has a relationship, the builder field drops out of the parent data completely. You won't need a JSON column in that case.

The builder starts empty unless you pass defaults or existing data. Existing related records or associations bring their tabs back automatically. Validation only runs on active tabs. Removing a relationship layout tab deletes its related record on save, right after validation. For `BelongsTo`, it dissociates the parent first. Removing a relationship field tab clears its selection through that field's relationship save callback. On color tabs, that only detaches the matching pivot tag. The color records themselves stay put. Removing a JSON tab deletes its key from the output.

Want every schema visible from the start? Pass all keys in `default()`. Turn off `addable()` and `deletable()` if users should always keep those tabs. Use `enabledItemName('seo')` to pick the initial tab. Set `label()` on a definition to change its tab label.

Need a deeper relationship? Wrap the builder in a group:

```php
use Filament\Schemas\Components\Group;

Group::make([
    TabBuilder::make('content')->schemas([
        // JSON tabs save on the related model, and relationship tabs resolve
        // from that model too.
    ]),
])->relationship('settings');

```

The builder itself has no `relationship()` method. Put a normal relationship repeater inside a tab if you need a collection relationship. Customize behavior and layout with `addAction()`, `deleteAction()`, `extraItemActions()`, `emptySchema()`, `internalLabel()`, `minItems()`, `maxItems()`, and `partiallyRenderAfterActionsCalled()`.