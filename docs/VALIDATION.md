# Validation record

Validation date: 4 August 2026.

## Completed

- Parsed both supplied Postman JSON files.
- Confirmed the supplied environment has blank consumer credentials and no embedded live secret.
- Reconciled every public SDK endpoint against Pesapal's current API 3.0 documentation.
- Ran `php -l` across every PHP source, test, migration, route, controller, model, and Blade example file.
- Ran a dependency-free DTO smoke test covering order payloads, IPN parsing/acknowledgement, amount normalization, and recurring dates.
- Ran a simulated HTTP client smoke test covering authentication, token reuse, hosted order creation, the corrected transaction-status endpoint, completed Visa status parsing, and successful handling of an all-null Pesapal `error` object.
- Validated `composer.json` and both generated Postman JSON files as JSON.
- Scanned the deliverable for accidental supplied tokens and secrets.

## Not executed

- No request was sent to Pesapal sandbox because the supplied `consumer_key_ke` and `consumer_secret_ke` values are blank.
- Composer dependencies are not installed in this execution environment, so PHPUnit, Testbench, Larastan, and Pint were not executed here. The repository includes CI configuration to execute them after dependencies are installed.
