<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        {{ isset($pesapalUrl) ? 'Complete Payment' : 'Secure Checkout' }}
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --bg: #f5f7fb;
            --card: #ffffff;
            --text: #101828;
            --muted: #667085;
            --border: #e4e7ec;
            --primary: #101828;
            --primary-hover: #1d2939;
            --danger: #b42318;
            --danger-bg: #fef3f2;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;

            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;

            background: var(--bg);
            color: var(--text);
        }

        body {
            min-height: 100vh;

            background:
                radial-gradient(
                    circle at top left,
                    #e8efff 0,
                    transparent 34rem
                ),
                var(--bg);
        }

        .page {
            min-height: 100vh;
        }

        .topbar {
            height: 72px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 28px;

            background: rgba(255, 255, 255, 0.95);
            border-bottom: 1px solid var(--border);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo {
            width: 42px;
            height: 42px;

            display: grid;
            place-items: center;

            border-radius: 12px;

            background: var(--primary);
            color: white;

            font-size: 18px;
            font-weight: 800;
        }

        .brand-copy strong {
            display: block;

            font-size: 15px;
            font-weight: 750;
        }

        .brand-copy span {
            display: block;

            margin-top: 2px;

            color: var(--muted);
            font-size: 12px;
        }

        .secure {
            color: #475467;

            font-size: 13px;
            font-weight: 600;
        }

        /*
        |--------------------------------------------------------------------------
        | FORM PAGE
        |--------------------------------------------------------------------------
        */

        .checkout-container {
            width: min(1080px, calc(100% - 32px));

            margin: 0 auto;

            padding: 40px 0 60px;
        }

        .checkout-grid {
            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                330px;

            gap: 24px;

            align-items: start;
        }

        .card {
            background: var(--card);

            border: 1px solid var(--border);
            border-radius: 22px;

            box-shadow:
                0 1px 2px rgba(16, 24, 40, .04),
                0 16px 40px rgba(16, 24, 40, .06);
        }

        .form-card {
            padding: 30px;
        }

        .eyebrow {
            margin-bottom: 10px;

            color: #475467;

            font-size: 12px;
            font-weight: 750;

            text-transform: uppercase;
            letter-spacing: .07em;
        }

        h1 {
            margin: 0;

            font-size: clamp(28px, 4vw, 38px);

            line-height: 1.1;
            letter-spacing: -.035em;
        }

        .description {
            max-width: 580px;

            margin: 12px 0 28px;

            color: var(--muted);

            font-size: 14px;
            line-height: 1.65;
        }

        .errors {
            margin-bottom: 22px;

            padding: 14px 16px;

            border: 1px solid #fecdca;
            border-radius: 12px;

            background: var(--danger-bg);
            color: var(--danger);

            font-size: 13px;
        }

        .errors ul {
            margin: 0;
            padding-left: 18px;
        }

        form {
            display: grid;
            gap: 20px;
        }

        .field {
            display: grid;
            gap: 8px;
        }

        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        label {
            color: #344054;

            font-size: 13px;
            font-weight: 650;
        }

        .input-wrap {
            position: relative;
        }

        input {
            width: 100%;
            height: 48px;

            padding: 0 14px;

            border: 1px solid #d0d5dd;
            border-radius: 11px;

            outline: none;

            background: white;
            color: var(--text);

            font: inherit;
            font-size: 14px;
        }

        input:focus {
            border-color: #98a2b3;

            box-shadow:
                0 0 0 4px
                rgba(152, 162, 179, .12);
        }

        input::placeholder {
            color: #98a2b3;
        }

        .amount-wrap input {
            padding-left: 62px;
        }

        .currency {
            position: absolute;

            left: 14px;
            top: 50%;

            transform: translateY(-50%);

            padding-right: 10px;

            border-right: 1px solid var(--border);

            color: #667085;

            font-size: 12px;
            font-weight: 750;
        }

        .hint {
            color: #98a2b3;
            font-size: 12px;
        }

        .pay-button {
            width: 100%;
            height: 52px;

            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;

            border: 0;
            border-radius: 12px;

            background: var(--primary);
            color: white;

            font: inherit;
            font-size: 14px;
            font-weight: 750;

            cursor: pointer;
        }

        .pay-button:hover {
            background: var(--primary-hover);
        }

        .summary {
            padding: 24px;

            position: sticky;
            top: 24px;
        }

        .summary h2 {
            margin: 0 0 18px;

            font-size: 17px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;

            padding: 10px 0;

            color: var(--muted);

            font-size: 13px;
        }

        .summary-row strong {
            color: #344054;
        }

        .divider {
            height: 1px;

            margin: 14px 0;

            background: var(--border);
        }

        .methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);

            gap: 8px;

            margin-top: 12px;
        }

        .method {
            min-height: 55px;

            display: grid;
            place-items: center;

            padding: 8px;

            border: 1px solid var(--border);
            border-radius: 10px;

            background: #fcfcfd;

            text-align: center;

            color: #475467;

            font-size: 11px;
            font-weight: 700;
        }

        .security-note {
            margin-top: 20px;

            padding: 14px;

            border-radius: 12px;

            background: #f8fafc;

            color: #667085;

            font-size: 12px;
            line-height: 1.55;
        }

        /*
        |--------------------------------------------------------------------------
        | PESAPAL IFRAME PAGE
        |--------------------------------------------------------------------------
        */

        .payment-container {
            width: min(1280px, calc(100% - 32px));

            margin: 24px auto;
        }

        .payment-shell {
            overflow: hidden;

            background: white;

            border: 1px solid var(--border);
            border-radius: 20px;

            box-shadow:
                0 1px 2px rgba(16, 24, 40, .04),
                0 16px 40px rgba(16, 24, 40, .07);
        }

        .payment-header {
            min-height: 82px;

            padding: 18px 24px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;

            border-bottom: 1px solid var(--border);
        }

        .payment-header h1 {
            font-size: 19px;
            letter-spacing: -.02em;
        }

        .payment-header p {
            margin: 5px 0 0;

            color: var(--muted);

            font-size: 12px;
        }

        .payment-amount {
            text-align: right;
            white-space: nowrap;
        }

        .payment-amount span {
            display: block;

            margin-bottom: 2px;

            color: var(--muted);

            font-size: 11px;
            text-transform: uppercase;
        }

        .payment-amount strong {
            font-size: 18px;
        }

        .iframe-wrap {
            width: 100%;

            background: white;
        }

        .pesapal-frame {
            display: block;

            width: 100%;

            height: calc(100vh - 190px);
            min-height: 680px;

            border: 0;

            background: white;
        }

        .payment-footer {
            padding: 11px 20px;

            border-top: 1px solid var(--border);

            background: #f9fafb;

            color: var(--muted);

            text-align: center;

            font-size: 12px;
        }

        @media (max-width: 850px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }

            .summary {
                position: static;
                order: -1;
            }
        }

        @media (max-width: 600px) {
            .topbar {
                height: 64px;
                padding: 0 16px;
            }

            .secure {
                display: none;
            }

            .checkout-container {
                width: 100%;
                padding: 0;
            }

            .checkout-grid {
                gap: 0;
            }

            .card {
                border-radius: 0;
                border-left: 0;
                border-right: 0;
            }

            .form-card {
                padding: 24px 18px 32px;
            }

            .summary {
                padding: 20px 18px;
            }

            .field-row {
                grid-template-columns: 1fr;
            }

            .payment-container {
                width: 100%;
                margin: 0;
            }

            .payment-shell {
                border-radius: 0;
                border-left: 0;
                border-right: 0;
            }

            .payment-header {
                padding: 15px 16px;
            }

            .payment-header h1 {
                font-size: 17px;
            }

            .pesapal-frame {
                height: calc(100vh - 155px);
                min-height: 650px;
            }
        }
    </style>
</head>

<body>

<div class="page">

    <header class="topbar">

        <div class="brand">

            <div class="brand-logo">
                D
            </div>

            <div class="brand-copy">
                <strong>
                    Development Work
                </strong>

                <span>
                    Secure checkout
                </span>
            </div>

        </div>

        <div class="secure">
            🔒 Secure payment
        </div>

    </header>


    {{-- ============================================================
         STATE 2: PESAPAL ORDER ALREADY CREATED
         ============================================================ --}}

    @if(isset($pesapalUrl) && $pesapalUrl)

        <main class="payment-container">

            <section class="payment-shell">

                <div class="payment-header">

                    <div>
                        <h1>
                            Complete your payment
                        </h1>

                        <p>
                            Choose your preferred payment method below.
                        </p>
                    </div>


                    @isset($payment)

                        <div class="payment-amount">

                            <span>
                                Amount
                            </span>

                            <strong>
                                {{ $payment->currency }}
                                {{ number_format((float) $payment->amount, 2) }}
                            </strong>

                        </div>

                    @endisset

                </div>


                <div class="iframe-wrap">

                    <iframe
                        class="pesapal-frame"
                        src="{{ $pesapalUrl }}"
                        title="Pesapal Secure Payment"
                        allow="payment"
                        scrolling="auto"
                    ></iframe>

                </div>


                <div class="payment-footer">
                    Payment processing is securely handled by Pesapal.
                </div>

            </section>

        </main>


    {{-- ============================================================
         STATE 1: INITIAL CHECKOUT FORM
         ============================================================ --}}

    @else

        <main class="checkout-container">

            <div class="checkout-grid">

                <section class="card form-card">

                    <div class="eyebrow">
                        Secure checkout
                    </div>

                    <h1>
                        Complete your payment
                    </h1>

                    <p class="description">
                        Enter your details below.
                        After you continue, Pesapal will open securely
                        inside this checkout page.
                    </p>


                    @if($errors->any())

                        <div class="errors">

                            <ul>

                                @foreach($errors->all() as $error)

                                    <li>
                                        {{ $error }}
                                    </li>

                                @endforeach

                            </ul>

                        </div>

                    @endif


                    <form
                        method="POST"
                        action="{{ route('pesapal.checkout') }}"
                    >
                        @csrf


                        <div class="field">

                            <label for="amount">
                                Amount
                            </label>

                            <div class="input-wrap amount-wrap">

                                <span class="currency">
                                    KES
                                </span>

                                <input
                                    id="amount"
                                    type="number"
                                    name="amount"
                                    min="1"
                                    step="0.01"
                                    value="{{ old('amount', 100) }}"
                                    required
                                >

                            </div>

                        </div>


                        <div class="field-row">

                            <div class="field">

                                <label for="first_name">
                                    First name
                                </label>

                                <input
                                    id="first_name"
                                    type="text"
                                    name="first_name"
                                    value="{{ old('first_name', 'Demo') }}"
                                    autocomplete="given-name"
                                >

                            </div>


                            <div class="field">

                                <label for="last_name">
                                    Last name
                                </label>

                                <input
                                    id="last_name"
                                    type="text"
                                    name="last_name"
                                    value="{{ old('last_name', 'Customer') }}"
                                    autocomplete="family-name"
                                >

                            </div>

                        </div>


                        <div class="field">

                            <label for="email">
                                Email address
                            </label>

                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email', 'developer@example.com') }}"
                                placeholder="developer@example.com"
                                autocomplete="email"
                            >

                        </div>


                        <div class="field">

                            <label for="phone">
                                Phone number
                            </label>

                            <input
                                id="phone"
                                type="text"
                                name="phone"
                                value="{{ old('phone') }}"
                                placeholder="2547XXXXXXXX"
                                autocomplete="tel"
                            >

                            <div class="hint">
                                Provide an email address or phone number.
                            </div>

                        </div>


                        <button
                            type="submit"
                            class="pay-button"
                        >
                            🔒 Continue to Pesapal
                        </button>

                    </form>

                </section>


                <aside class="card summary">

                    <h2>
                        Payment summary
                    </h2>

                    <div class="summary-row">
                        <span>Currency</span>
                        <strong>KES</strong>
                    </div>

                    <div class="summary-row">
                        <span>Processor</span>
                        <strong>Pesapal</strong>
                    </div>


                    <div class="divider"></div>


                    <div class="summary-row">
                        <span>
                            Payment methods
                        </span>
                    </div>


                    <div class="methods">

                        <div class="method">
                            💳<br>
                            Card
                        </div>

                        <div class="method">
                            📱<br>
                            Mobile
                        </div>

                        <div class="method">
                            🏦<br>
                            Bank
                        </div>

                    </div>


                    <div class="security-note">
                        🔒 Your payment is processed securely by Pesapal.
                        Payment credentials are not stored by this Laravel
                        application.
                    </div>

                </aside>

            </div>

        </main>

    @endif

</div>

</body>
</html>