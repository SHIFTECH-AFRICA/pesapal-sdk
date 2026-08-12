ARG PHP_VERSION=8.3

FROM php:${PHP_VERSION}-fpm-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    COMPOSER_PROCESS_TIMEOUT=1200

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        unzip \
        libcurl4-openssl-dev \
        libicu-dev \
        libonig-dev \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        dom \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_pgsql \
        xml \
        xmlwriter \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/entrypoint.sh /usr/local/bin/pesapal-example-entrypoint

RUN chmod +x /usr/local/bin/pesapal-example-entrypoint

WORKDIR /workspace/examples/laravel

ENTRYPOINT ["pesapal-example-entrypoint"]
CMD ["php-fpm", "-F"]
