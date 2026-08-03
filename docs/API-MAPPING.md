# Pesapal API 3.0 mapping

| SDK method | HTTP | Official path |
|---|---:|---|
| `authenticate()` | POST | `/Auth/RequestToken` |
| `registerIpn()` | POST | `/URLSetup/RegisterIPN` |
| `listIpns()` | GET | `/URLSetup/GetIpnList` |
| `submitOrder()` | POST | `/Transactions/SubmitOrderRequest` |
| `getTransactionStatus()` | GET | `/Transactions/GetTransactionStatus` |
| `refund()` | POST | `/Transactions/RefundRequest` |
| `cancelOrder()` | POST | `/Transactions/CancelOrder` |

## Source reconciliation

The supplied Postman environment defines the sandbox host as `cybqa.pesapal.com` with `/pesapalv3/api/`, which matches the official sandbox base URL.

The supplied collection's raw `GetTransactionStatus` URL is missing the `Transactions/` segment. Its own long-form description, and Pesapal's current official documentation, identify the endpoint as `/Transactions/GetTransactionStatus`. This SDK uses the official path.

The supplied `transactions/stk` request is a direct mobile-money request and is not part of the public hosted-card flow. It is intentionally excluded from the first stable card SDK surface. A future module can add it after Pesapal supplies the restricted contract and test credentials.
