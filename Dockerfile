FROM php:8.5-cli-bookworm AS composer

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-install \
        bcmath \
        intl \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts


FROM node:24-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY app ./app
COPY resources ./resources
COPY vite.config.js ./
COPY --from=composer /app/vendor ./vendor

RUN npm run build

FROM php:8.5-fpm-bookworm AS app

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-install \
        bcmath \
        intl \
        pdo_mysql \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY . .

# Never carry development-generated Laravel caches into production.
RUN find bootstrap/cache -mindepth 1 ! -name '.gitignore' -delete

COPY --from=composer /app/vendor ./vendor

COPY --from=frontend /app/public/build ./public/build

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

RUN mkdir -p public/storage \
    && chown -R www-data:www-data public

EXPOSE 9000

CMD ["php-fpm"]
