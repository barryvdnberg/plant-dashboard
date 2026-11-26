# Docker deployment

`compose.yaml` sets up the Helix Garden deployment. It runs PHP-FPM, Nginx, and MySQL 8.4. It expects a reverse proxy in front of `[https://helixgarden.com](https://helixgarden.com)`. Host storage lives under `/volume1/docker/helixgarden`. Swap out the URL and paths if deploy changes. Lock down port 8081 so only your reverse proxy can reach it. Make sure the proxy overwrites incoming forwarded headers.

## Configure the deployment

Copy the [example](../.env.docker.example) to `.env.docker`. Fill in `APP_KEY`, `DB_PASSWORD`, and `DB_ROOT_PASSWORD`. For a brand-new setup, run `php artisan key:generate --show` locally to grab a key. Keep your existing key and MySQL passwords when redeploying.

Create the storage and MySQL folders on the host first. Inside the Laravel storage folder, make these directories:

* `app/private`
* `app/public`
* `framework/cache/data`
* `framework/sessions`
* `framework/views`
* `logs`

Make sure the PHP-FPM user can write to all of them. Back up your database and uploaded files regularly.

## Build and start

```sh
docker compose --env-file .env.docker config --quiet
docker compose --env-file .env.docker build app
docker compose --env-file .env.docker up -d mysql
docker compose --env-file .env.docker run --rm app php artisan migrate --force
docker compose --env-file .env.docker up -d

```

After migrating and starting the app, choose a [seeding option](../README.md#seed-the-database). To load the starter catalog and reference data, run this command.

```sh
docker compose --env-file .env.docker exec app php artisan db:seed --force
```

With reference data already loaded, add `--class=StarterPlantCatalogSeeder` to run the plant catalog step by itself. For reference data only, run the four commands in the README in order. Prefix each command with `docker compose --env-file .env.docker exec app` and append `--force`.

Set up a persistent queue worker to handle catalog imports and exports. Inside the app container, run `php artisan queue:work`.

PHP and Nginx share the `public-files` volume. Docker fills it from the app image on the first run. When you redeploy, refresh those files from the new image so Nginx serves the latest Vite manifest and bundles. MySQL data and Laravel storage stay in separate persistent host mounts.
