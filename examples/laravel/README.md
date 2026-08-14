# Pesapal SDK — Runnable Laravel Example

This directory is a standalone Laravel application used to exercise the SDK like a real consumer application.

The SDK is **not downloaded from Packagist**. Composer resolves it from the repository root via a local path repository:

```json
"repositories": [
    {
        "type": "path",
        "url": "../..",
        "options": {
            "symlink": true,
            "versions": {
                "shiftechafrica/pesapal-laravel-sdk": "dev-main"
            }
        }
    }
],
"require": {
    "shiftechafrica/pesapal-laravel-sdk": "dev-main"
}
```

## Run with Docker

From this directory:

```bash
docker compose up --build -d
```

No fixed Windows host port is reserved. Docker allocates an available port for Nginx. Find it with:

```bash
docker compose port nginx 80
```

Example output:

```text
0.0.0.0:49172
```

Open `http://localhost:49172` using the port Docker returned on your machine.

PostgreSQL is reachable only inside the Compose network as `pgsql:5432`; it is not published to Windows.

## Pesapal credentials

The first container start creates `.env` from `.env.example`. Set your sandbox credentials and public callback/IPN URLs in `.env`:

```dotenv
PESAPAL_ENVIRONMENT=sandbox
PESAPAL_CONSUMER_KEY=...
PESAPAL_CONSUMER_SECRET=...
PESAPAL_NOTIFICATION_ID=...
PESAPAL_IPN_URL=https://your-public-url/api/payments/pesapal/ipn
PESAPAL_CALLBACK_URL=https://your-public-url/payments/pesapal/callback
```

The callback/IPN URLs must be publicly reachable by Pesapal; use your normal tunnel/reverse-proxy workflow for local development.

## Useful commands

```bash
# Shell
docker compose exec app bash

# Verify Composer installed the local SDK
docker compose exec app composer show shiftechafrica/pesapal-laravel-sdk

# Laravel routes
docker compose exec app php artisan route:list

# Register an IPN URL once your public URL is available
docker compose exec app php artisan pesapal:ipn:register "https://your-public-url/api/payments/pesapal/ipn"

# Reset demo database
docker compose exec app php artisan migrate:fresh

# Stop
docker compose down

# Stop and remove the demo database volume
docker compose down -v
```

Because the path repository uses `symlink: true`, edits to the SDK source at the repository root are consumed by this Dockerized Laravel demo without publishing the package.


docker compose exec app php artisan pesapal:ipn:register "https://d108-154-159-254-213.ngrok-free.app/api/payments/pesapal/ipn" --method=POST