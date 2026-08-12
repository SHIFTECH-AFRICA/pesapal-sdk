#!/bin/sh
set -eu

APP_DIR=/workspace/examples/laravel
cd "$APP_DIR"

if [ ! -f .env ]; then
    echo "[bootstrap] Creating .env from .env.example"
    cp .env.example .env
fi

composer_input_hash() {
    if [ -f composer.lock ]; then
        cat composer.json composer.lock | sha256sum | awk '{print $1}'
    else
        sha256sum composer.json | awk '{print $1}'
    fi
}

CURRENT_HASH="$(composer_input_hash)"
INSTALLED_HASH=""

if [ -f vendor/.composer-input-hash ]; then
    INSTALLED_HASH="$(cat vendor/.composer-input-hash)"
fi

if [ ! -f vendor/autoload.php ] || [ "$CURRENT_HASH" != "$INSTALLED_HASH" ]; then
    echo "[composer] Installing Laravel demo dependencies"
    echo "[composer] SDK source: /workspace (Composer path repository ../..)"

    composer install \
        --no-interaction \
        --prefer-dist \
        --no-progress \
        --optimize-autoloader

    # composer install can create composer.lock on first boot, so hash again afterwards.
    composer_input_hash > vendor/.composer-input-hash
else
    echo "[composer] Dependencies are already current; skipping install"
fi

if ! grep -Eq '^APP_KEY=base64:.+' .env; then
    echo "[laravel] Generating APP_KEY"
    php artisan key:generate --force
fi

echo "[laravel] Running migrations"
php artisan migrate --force

echo "[runtime] Starting: $*"
exec "$@"
