# Helix Garden

Helix Garden is a Laravel application for maintaining a plant information database and managing the plants in a garden. It has two Filament panels that share the same database and user accounts.

| Panel | Who uses it | What it does |
| --- | --- | --- |
| `/admin` | Administrators | Manage plant families, genera, species, plants, cultivars, and translations. Import and export catalog records and maintain reference lists such as colors and soil types. |
| `/app` | Signed-in users | Manage their gardens and plantings and view garden actions. |

## Local development

Use PHP 8.4 or 8.5, Composer 2, and Node.js 22.12 or newer. PHP 8.5 and Node.js 24 match the Docker build. Enable the PHP extensions required by Composer, including `intl`, and install the PDO driver for your database.

On a fresh checkout, create the local environment file and an empty SQLite database before installing dependencies.

```sh
cp .env.example .env
touch database/database.sqlite
```

In PowerShell, use these commands instead.

```powershell
Copy-Item .env.example .env
New-Item -ItemType File database/database.sqlite
```

Install dependencies and generate a local application key.

```sh
composer install
php artisan key:generate
npm ci
```

### Seed the database

| Starting point | What to run | What you get |
| --- | --- | --- |
| Fresh database, schema only | `php artisan migrate` | Empty catalog and reference tables. |
| Fresh database, starter catalog | `php artisan migrate --seed` | Reference data and the bundled plant catalog. |
| Reference data only | Run `php artisan migrate` if needed, then the four reference seeders below | Languages, colors, garden styles, and soil types. You can then build your own plant catalog. |
| Reference data already loaded, starter catalog only | `php artisan db:seed --class=StarterPlantCatalogSeeder` | Families through cultivars, using the existing languages, colors, and soil types. |
| Migrated database, including one with reference data or some starter entries | `php artisan db:seed` | The full seed. Existing entries that match the bundled records are reused. |

The full seed loads reference data first, then a fixed [starter catalog](database/seeders/StarterPlantCatalog.php) with three families, six genera, twelve species, one plant per species, and six named cultivars. Each plant also gets an unnamed base cultivar. Species and plant common names are seeded in Dutch and English.

Base and named cultivars get a plant type. The other details vary by profile. For example, ‘Robella’ has a plant type, while ‘Alba’ also has foliage, environment, and blooming details. Seeded environment records use the app's default pH range of 6–8.

Rerunning either catalog seed adds missing records, translations, and links. The full seed matches languages by code. Both match catalog entries by name under their parent where applicable. Existing translations and detail fields take precedence over seed defaults, including blank fields on existing detail records. If you rename a starter entry, a later seed can create a new entry under the bundled name.

For reference data only, run the language seeder first so the other three can add translated names.

```sh
php artisan db:seed --class=LanguageSeeder
php artisan db:seed --class=ColorSeeder
php artisan db:seed --class=GardenStyleSeeder
php artisan db:seed --class=SoilTypeSeeder
```

For synthetic data in tests or local development, call Faker factories explicitly. With development dependencies installed, run this in `php artisan tinker` to create a cultivar, its taxonomy, and its related plant details.

```php
App\Models\Cultivar::factory()->withDetails()->create();
```

### Create a local account

Run `php artisan make:filament-user --panel=app` and follow the prompts. That account can sign into `/app`. To give it access to `/admin`, open `php artisan tinker` and use the account's email in the following command.

```php
App\Models\User::where('email', 'you@example.test')->firstOrFail()
    ->forceFill(['is_admin' => true])->save();
```

### Run the app

Run `composer dev` to start Laravel, the queue listener, and Vite. Visit `http://127.0.0.1:8000/app` or `http://127.0.0.1:8000/admin`.

With Laravel Herd, set `APP_URL` in `.env` to your Herd site URL and run `npm run dev` for asset changes. Run `php artisan queue:work` when testing queued imports or exports. To serve compiled assets, stop Vite and run `npm run build`.

To use a local MySQL database, create it first and set `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env` before installing and migrating.

## Tests

The test configuration uses in-memory SQLite. Install Chromium before running the Pest browser suite.

```sh
composer test:unit
composer test:feature
npx playwright install chromium
composer test:browser
```

Run a single file with `php artisan test path/to/Test.php`. Use `php vendor/bin/pint --test path/to/file.php` to check formatting in a file you edited.

## Project layout

| Path | Responsibility |
| --- | --- |
| `app/Filament/Resources/` | Admin catalog resources, pages, and tables |
| `app/Filament/App/` | The garden panel's pages and resources |
| `app/Filament/Forms/` and `app/Filament/Schemas/` | Shared form builders, fields, and layouts |
| `app/Models/`, `app/Policies/`, and `database/` | Domain models, access rules, migrations, and seed data |
| `resources/lang/` | Application interface translations |

## Documentation

- [Model names and localization](docs/localization.md)
- [TabBuilder](docs/tab-builder.md)
- [Schema visibility](docs/schema-visibility.md)
- [Docker deployment](docs/deployment.md)
