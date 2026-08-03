# Shiftech Africa Pesapal Laravel SDK

A production-grade Laravel SDK for Pesapal API 3.0 hosted payments, designed for Kenyan and East African applications that need Visa and Mastercard acceptance without handling raw card data.

## What this SDK does

- Authenticates and safely caches Pesapal's short-lived bearer token.
- Registers and lists IPN endpoints.
- Creates hosted checkout orders and returns the Pesapal redirect URL.
- Verifies callback/IPN payments through `GetTransactionStatus`.
- Supports recurring card enrollment fields, refunds, and eligible order cancellation.
- Provides Laravel auto-discovery, a facade, typed DTOs, exceptions, and Artisan commands.
- Includes an idempotent Laravel integration example.

## Important card architecture

Your Laravel application must **not** collect card number, CVV, or expiry data. `SubmitOrderRequest` returns a hosted `redirect_url`; Pesapal presents the available payment methods, including enabled card methods, and handles the sensitive card flow.

## Installation

```bash
composer require shiftechafrica/pesapal-laravel-sdk
php artisan vendor:publish --tag=pesapal-config
```

Add credentials and URLs:

```dotenv
PESAPAL_ENVIRONMENT=sandbox
PESAPAL_CONSUMER_KEY=your-consumer-key
PESAPAL_CONSUMER_SECRET=your-consumer-secret
PESAPAL_NOTIFICATION_ID=your-registered-ipn-id
PESAPAL_IPN_URL=https://app.example.com/api/payments/pesapal/ipn
PESAPAL_CALLBACK_URL=https://app.example.com/payments/pesapal/callback
PESAPAL_CANCELLATION_URL=https://app.example.com/payments/cancelled
PESAPAL_CURRENCY=KES
```

Register an IPN once per environment:

```bash
php artisan pesapal:ipn:register https://app.example.com/api/payments/pesapal/ipn --method=POST
php artisan pesapal:ipn:list
```

Copy the returned IPN ID into `PESAPAL_NOTIFICATION_ID`.

## Create a card checkout

```php
use ShiftechAfrica\Pesapal\Data\BillingAddress;
use ShiftechAfrica\Pesapal\Data\OrderRequest;
use ShiftechAfrica\Pesapal\Http\PesapalClient;

public function pay(PesapalClient $pesapal)
{
    $order = $pesapal->submitOrder(new OrderRequest(
        id: 'INV-2026-0001',
        amount: '2500.00',
        description: 'Invoice payment',
        billingAddress: new BillingAddress(
            emailAddress: 'customer@example.com',
            phoneNumber: '0712345678',
            countryCode: 'KE',
            firstName: 'Amina',
            lastName: 'Kamau',
        ),
    ));

    // Redirect to Pesapal's hosted Visa/Mastercard/mobile-money checkout.
    return redirect()->away($order->redirectUrl);
}
```

## Process callback or IPN

Callback and IPN parameters do not contain a trustworthy payment result. Parse the notification, query Pesapal, and reconcile against your local payment:

```php
use Illuminate\Http\Request;
use ShiftechAfrica\Pesapal\Data\IpnNotification;
use ShiftechAfrica\Pesapal\Http\PesapalClient;

public function ipn(Request $request, PesapalClient $pesapal)
{
    $notification = IpnNotification::fromArray(
        array_merge($request->query(), $request->all())
    );

    $localPayment = Payment::where('merchant_reference', $notification->orderMerchantReference)->firstOrFail();

    $transaction = $pesapal->verifyPayment(
        orderTrackingId: $notification->orderTrackingId,
        merchantReference: $notification->orderMerchantReference,
        amount: $localPayment->amount,
        currency: $localPayment->currency,
        requireCompleted: false,
    );

    // Update the local record idempotently. Deliver only if COMPLETED.

    return response()->json($notification->acknowledgement(200));
}
```

See `examples/laravel` for a database-backed implementation with unique constraints and row locking.

## Recurring Visa/Mastercard payments

```php
use ShiftechAfrica\Pesapal\Data\SubscriptionDetails;
use ShiftechAfrica\Pesapal\Enums\SubscriptionFrequency;

$order = new OrderRequest(
    id: 'SUB-1001',
    amount: 1500,
    description: 'Monthly membership',
    billingAddress: $billingAddress,
    accountNumber: 'MEMBER-1001',
    subscriptionDetails: new SubscriptionDetails(
        startDate: '04-08-2026',
        endDate: '04-08-2027',
        frequency: SubscriptionFrequency::Monthly,
    ),
);
```

The customer still confirms enrollment on Pesapal's hosted payment page.

## Refund

```php
$response = $pesapal->refund(
    confirmationCode: $payment->confirmation_code,
    amount: '500.00',
    username: auth()->user()->name,
    remarks: 'Partial refund approved',
);
```

A successful API response means the refund request was accepted for processing, not that funds have already settled back to the customer.

## Supported versions

- PHP 8.2+
- Laravel 10, 11, 12, and 13
- Pesapal API 3.0

## Development

```bash
composer install
composer test
composer analyse
composer format
```

## Documentation

- `docs/ARCHITECTURE.md`
- `docs/API-MAPPING.md`
- `docs/SECURITY.md`
- `docs/RESEARCH-NOTES.md`
- `resources/postman/` for a sanitized environment and corrected collection

## License

MIT License. Copyright Shiftech Africa.
