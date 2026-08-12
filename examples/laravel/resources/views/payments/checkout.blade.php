<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesapal SDK Laravel Example</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 720px; margin: 3rem auto; padding: 0 1rem; }
        form { display: grid; gap: 1rem; }
        label { display: grid; gap: .35rem; }
        input { padding: .7rem; font: inherit; }
        button { padding: .8rem 1rem; font: inherit; cursor: pointer; }
        code { background: #f2f2f2; padding: .15rem .35rem; }
    </style>
</head>
<body>
    <h1>Pesapal Laravel SDK Demo</h1>
    <p>This application consumes the SDK from the local Composer path repository <code>../..</code>.</p>

    <form method="POST" action="{{ route('pesapal.checkout') }}">
        @csrf

        <label>
            Amount (KES)
            <input type="number" name="amount" min="1" step="0.01" value="100" required>
        </label>

        <label>
            Email
            <input type="email" name="email" value="developer@example.com">
        </label>

        <label>
            Phone
            <input type="text" name="phone" placeholder="2547XXXXXXXX">
        </label>

        <label>
            First name
            <input type="text" name="first_name" value="Demo">
        </label>

        <label>
            Last name
            <input type="text" name="last_name" value="Customer">
        </label>

        <button type="submit">Pay with Pesapal</button>
    </form>
</body>
</html>
