# Security model

## Card data

This package never accepts, logs, stores, or transmits PAN, CVV, expiry date, or cardholder authentication data. Visa and Mastercard entry happens on Pesapal's hosted redirect or iframe page.

## Trust boundaries

- A callback or IPN is only a notification that something changed.
- Never mark a local invoice paid from callback/IPN parameters alone.
- Fetch `GetTransactionStatus` using `OrderTrackingId`.
- Compare merchant reference, amount, and currency to the immutable local order.
- Grant value only when the verified status is `COMPLETED`.
- Persist the confirmation code because refunds use it.

## Idempotency

Use a unique merchant reference in your database and a unique Pesapal tracking ID. Process IPNs inside a database transaction with a row lock. Repeated notifications should update the same payment record without delivering the product twice.

## Secrets and logging

Keep consumer credentials in environment variables or a secret manager. Do not log bearer tokens, complete request headers, or unredacted provider payloads in production. The `payment_account` returned by Pesapal is already masked, but should still be treated as sensitive payment metadata.

## TLS

SSL verification is enabled by default. Do not disable `PESAPAL_VERIFY_SSL` in production.
