# Research notes

Research date: 4 August 2026.

## Primary sources reviewed

- Pesapal API 3.0 API reference: `https://developer.pesapal.com/how-to-integrate/e-commerce/api-30-json/api-reference`
- Authentication: `https://developer.pesapal.com/how-to-integrate/e-commerce/api-30-json/authentication`
- Register IPN: `https://developer.pesapal.com/how-to-integrate/e-commerce/api-30-json/registeripnurl`
- Get IPN list: `https://developer.pesapal.com/how-to-integrate/e-commerce/api-30-json/getregisteredipn`
- Submit order: `https://developer.pesapal.com/how-to-integrate/e-commerce/api-30-json/submitorderrequest`
- Transaction status: `https://developer.pesapal.com/how-to-integrate/e-commerce/api-30-json/gettransactionstatus`
- Recurring payments: `https://developer.pesapal.com/how-to-integrate/e-commerce/api-30-json/recurringpayments`
- Refunds: `https://developer.pesapal.com/how-to-integrate/e-commerce/api-30-json/refund-request`
- Order cancellation: `https://developer.pesapal.com/how-to-integrate/e-commerce/api-30-json/order-cancellation-api`
- Laravel package development: `https://laravel.com/docs/13.x/packages`
- Existing Shiftech Africa package conventions: `https://github.com/SHIFTECH-AFRICA/pam-php-sdk`

## Findings reflected in code

- Pesapal tokens are short-lived, so the SDK caches with a safety margin and refreshes once after HTTP 401.
- IPN registration is required before submitting an order because `notification_id` is mandatory.
- Card entry is hosted by Pesapal through the returned `redirect_url`; the SDK deliberately has no PAN/CVV fields.
- Callback and IPN parameters are notifications, not proof of payment. The SDK queries transaction status and offers strict reconciliation.
- Transaction status uses `Transactions/GetTransactionStatus`. This corrects the raw URL in the supplied collection.
- Status codes map to invalid, completed, failed, and reversed; pending is also represented because Pesapal cancellation documentation explicitly covers pending orders.
- Recurring card enrollment supports Visa and Mastercard and still requires customer consent on the hosted page.
- Card refunds may be partial or full. An accepted refund request is not the same as a completed refund.
- Cancellation is intended for failed or pending orders and cannot cancel an already processed payment.

## Supplied Postman materials

The supplied sandbox environment contained blank credential values, the sandbox host, callback/IPN placeholders, KES currency, and no live secret. The supplied collection was used as a secondary implementation fixture. Where it conflicted with Pesapal's current official documentation, the official endpoint was used and the correction was documented.
