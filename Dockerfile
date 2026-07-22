# syntax=docker/dockerfile:1.7

ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-apache-bookworm AS php-base

ARG MONGODB_VERSION=1.21.5
ARG BUILD_JOBS=4

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        libicu-dev \
        libonig-dev \
        libssl-dev \
        libzip-dev \
        unzip \
        ${PHPIZE_DEPS} \
    && docker-php-ext-install -j"${BUILD_JOBS}" \
        bcmath \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        sockets \
        zip \
    && MAKEFLAGS="-j${BUILD_JOBS}" pecl install "mongodb-${MONGODB_VERSION}" \
    && docker-php-ext-enable mongodb \
    && a2enmod expires headers rewrite \
    && apt-get purge -y --auto-remove \
        libicu-dev \
        libonig-dev \
        libssl-dev \
        libzip-dev \
        ${PHPIZE_DEPS} \
    && apt-get install -y --no-install-recommends \
        curl \
        libicu72 \
        libonig5 \
        libssl3 \
        libzip4 \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

FROM php-base AS php-dependencies

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --classmap-authoritative \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist

COPY . .

RUN composer dump-autoload \
    --classmap-authoritative \
    --no-dev \
    --no-interaction

FROM node:22-bookworm-slim AS frontend-assets

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci --no-audit --no-fund

COPY --from=php-dependencies /app/vendor ./vendor
COPY resources ./resources
COPY vite.config.js ./

RUN npm run build

FROM php-base AS runtime

ARG APP_VERSION=dev
ARG VCS_REF=unknown

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_VERSION=${APP_VERSION} \
    APP_COMMIT=${VCS_REF} \
    LOG_CHANNEL=stderr

LABEL org.opencontainers.image.title="PV Market Admin Panel" \
      org.opencontainers.image.description="PV Market Laravel administration panel" \
      org.opencontainers.image.version="${APP_VERSION}" \
      org.opencontainers.image.revision="${VCS_REF}"

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY --from=php-dependencies --chown=www-data:www-data /app ./
COPY --from=frontend-assets --chown=www-data:www-data /app/public/build ./public/build

RUN mkdir -p \
        bootstrap/cache \
        storage/app/private \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
    && ln -sfn ../storage/app/public public/storage \
    && chown -R www-data:www-data bootstrap/cache storage

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl --fail --silent --show-error http://127.0.0.1/up > /dev/null || exit 1

CMD ["apache2-foreground"]
