FROM php:8.4-apache-bookworm AS php-base

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
        git \
        gosu \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libssl-dev \
        libzip-dev \
        pkg-config \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath gd intl mbstring opcache pcntl zip \
    && pecl install mongodb-1.21.5 redis-6.3.0 \
    && docker-php-ext-enable mongodb redis \
    && a2enmod headers remoteip rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/pv-market.ini

WORKDIR /var/www/html

FROM php-base AS vendor-production
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

FROM php-base AS vendor-test
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN composer install \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts

FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM php-base AS test
COPY . .
COPY --from=vendor-test /var/www/html/vendor ./vendor
RUN cp .env.example .env \
    && php artisan package:discover --ansi
CMD ["php", "artisan", "test"]

FROM php-base AS runtime
COPY . .
COPY --from=vendor-production /var/www/html/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY docker/entrypoint.sh /usr/local/bin/pv-market-entrypoint

RUN mkdir -p bootstrap/cache \
    && php artisan package:discover --ansi \
    && chmod 0755 /usr/local/bin/pv-market-entrypoint \
    && mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=7s --start-period=30s --retries=3 \
  CMD curl --fail --silent --show-error http://127.0.0.1/up >/dev/null || exit 1

ENTRYPOINT ["pv-market-entrypoint"]
CMD ["apache2-foreground"]
