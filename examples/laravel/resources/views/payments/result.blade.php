<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment result</title>
</head>
<body>
    <main>
        <h1>Payment {{ strtolower($transaction->paymentStatus->value) }}</h1>
        <p>Reference: {{ $payment->merchant_reference }}</p>
        <p>Amount: {{ $payment->currency }} {{ $payment->amount }}</p>
        <p>Method: {{ $transaction->paymentMethod ?? 'Not available' }}</p>
    </main>
</body>
</html>
