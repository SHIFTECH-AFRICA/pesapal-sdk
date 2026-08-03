# Architecture decision record

## Decision

Implement Pesapal card payments as hosted checkout orchestration rather than a server-side card-acquiring API.

## Flow

1. Authenticate with consumer key and secret.
2. Register a public IPN endpoint and persist the returned `ipn_id`.
3. Create a local pending payment with a unique merchant reference.
4. Submit an order to Pesapal.
5. Redirect the customer to `redirect_url` or embed that URL in an iframe.
6. Receive callback and IPN notifications.
7. Query transaction status from Pesapal.
8. Reconcile tracking id, merchant reference, amount, and currency.
9. Mark paid only for `COMPLETED`.

## Token handling

Pesapal access tokens are short-lived. The SDK caches the token with a safety margin and performs one forced re-authentication when an authenticated request receives HTTP 401. It does not automatically retry transport failures for state-changing requests because an ambiguous network failure could otherwise create duplicate effects.

## API errors

Pesapal can communicate failure through HTTP status, the response `status` field, or the documented `error` object. The client normalizes all three into `ApiException` while preserving the provider response for controlled diagnostics.
