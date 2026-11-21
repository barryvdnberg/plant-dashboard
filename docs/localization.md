# Model names and localization

Models implement `Localization` via `HasLocalization`. That trait wires up the relationships, translation writer, name resolution, and localization query joins. Shared logic lives in `HasLocalizedName`, `SelectsLocalization`, and `JoinsBelongsToPaths` so you can mix localization and belongs-to traits cleanly.

```php
$cultivar->scientific_name;              // Prunus yedoensis 'Akebono'
$cultivar->getTranslation('en');         // Own translation, or null
$cultivar->getTranslatedName('nl');      // Localized plant + localized cultivar
$cultivar->getTranslatedName('nl', 0);   // Own translation, falling back to name
$cultivar->display_name;                // Applies admin preference, localized elsewhere
$cultivar->getLocalizedDisplayName();   // Own translation, otherwise translated name

```

**Name resolution and fallbacks**

* **Plants** just use their stored `name` directly. They don't walk or translate the rest of the taxonomy.
* **Species** combine genus and species.
* **Cultivars** take the plant's scientific name and wrap the cultivar epithet in quotes (unless it's already quoted).

The default translation depth is `0` for everything except cultivars. Cultivars default to `1`. Each level falls back to its own stored name if a translation is missing. Unnamed cultivars fall back to their plant's name, though a cultivar's own translation still wins for display. Blank cultivar names get saved as `null` so they keep acting as default attribute holders. Plant relationships also recognize legacy empty-string names.

If a cultivar doesn't have a plant, it uses the translated UI placeholder `Unknown :name`. Unsupported locales fall back to stored names instead of silently defaulting to English.

**Database rows and query joins**
New name translations set `field` to `name`. Legacy `null`-field rows still work too. Explicit `name` translations take priority, and blank values or other fields get ignored during name resolution. Query joins pick just one translation per model so old duplicate rows won't multiply your results.

When fetching lists of models, eager load both translations and parents:

```php
$cultivars = Cultivar::query()->withLocalizedData(depth: 1)->select('cultivars.*')->get();
// display_name uses the current application locale; all languages are eager loaded.

```

`withLocalizedData(locale: 'nl')` only controls SQL joins and projections. Model accessors always use the app locale or whatever locale you pass directly to `getTranslatedName()`. `addLocalizationSelect()` projects nullable `_translation` columns. `addLocalizationCoalesce()` projects `_label` columns with stored-name fallbacks. Those projections apply to each individual model and joined parent, so use `display_name` when you want a full cultivar label.

**Seeding and caching**
Seed catalogs update names with `replaceNameTranslations(['en' => '...', 'nl' => '...'])`. This leaves other translated fields alone. It also fires normal model events for deletions and creations and clears the model's loaded translations. `HasTranslations` adapts catalogs keyed by stored model names to call this method.

Color definitions and localized labels use persistent caches with request-level memoization. `CachesLocalizedLabels` listens to translation events to clear affected language maps (including the previous language if a translation moves). Changing a color clears the whole catalog and all language maps. Label maps always return fresh collections so callers can't accidentally mutate cached data. The language-options repeater uses eager-loaded translations instead of a shared static cache across locales. Since bulk SQL writes bypass model events, you'll need to call `Color::forgetLocalizedLabels()` manually after running them.

## Admin profile preferences

The admin user menu links to Filament's standard profile page at `/admin/profile`. It keeps Filament's default account and password handling and adds regional and display settings on top. One language setting controls both the UI and translated model names. Supported interface languages live in `config/localization.php`. Leaving the locale blank falls back to `config('app.locale')`.

`users.name_display_preference` switches between scientific and translated names (defaulting to scientific). For granular control, `users.name_display_overrides` holds a nullable JSON map with keys for `families`, `genera`, `species`, `plants`, and `cultivars`. Any missing key inherits the global setting:

```json
{"cultivars": "translated"}

```

`NameDisplay` and `NameDisplayGroup` define the valid choices and model groups. `User::nameDisplayFor()` handles the inheritance. `ApplyAdminPreferences` turns on the scoped `NameDisplayPreferences` resolver only for authenticated admin routes and their Livewire updates. The request context gets cleared when the HTTP request finishes. Other panels and background jobs keep their normal localized-name behavior. Non-botanical labels like colors are always localized.

Admin botanical tables show a derived display-name column right next to the editable stored name. Select labels, record titles, and display names all use the same model resolver. Explicit calls to `scientific_name`, `getTranslation()`, `getTranslatedName()`, and `getLocalizedDisplayName()` ignore the user's display preference and work the same everywhere.