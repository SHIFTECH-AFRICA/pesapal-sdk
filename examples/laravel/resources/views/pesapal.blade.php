<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Complete Payment | Pesapal</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont,
                "Segoe UI", sans-serif;
            background: #f5f7fb;
            color: #101828;
        }

        body {
            min-height: 100vh;
        }

        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            height: 72px;
            padding: 0 28px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            background: #ffffff;
            border-bottom: 1px solid #e4e7ec;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo {
            width: 42px;
            height: 42px;

            display: grid;
            place-items: center;

            border-radius: 12px;

            background: #101828;
            color: #ffffff;

            font-weight: 800;
            font-size: 18px;
        }

        .brand-text strong {
            display: block;
            font-size: 15px;
        }

        .brand-text span {
            display: block;
            margin-top: 2px;
            color: #667085;
            font-size: 12px;
        }

        .secure {
            color: #475467;
            font-size: 13px;
            font-weight: 600;
        }

        .main {
            flex: 1;
            width: min(1250px, calc(100% - 32px));
            margin: 24px auto;
        }

        .payment-card {
            overflow: hidden;

            background: #ffffff;

            border: 1px solid #e4e7ec;
            border-radius: 20px;

            box-shadow:
                0 2px 4px rgba(16, 24, 40, 0.04),
                0 16px 40px rgba(16, 24, 40, 0.08);
        }

        .payment-header {
            min-height: 84px;

            padding: 18px 24px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;

            border-bottom: 1px solid #e4e7ec;
        }

        .payment-header h1 {
            margin: 0;
            font-size: 20px;
        }

        .payment-header p {
            margin: 5px 0 0;
            color: #667085;
            font-size: 13px;
        }

        .amount {
            text-align: right;
            white-space: nowrap;
        }

        .amount span {
            display: block;
            margin-bottom: 3px;
            color: #667085;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .amount strong {
            font-size: 19px;
        }

        .frame-container {
            position: relative;
            width: 100%;
            background: #ffffff;
        }

        .pesapal-frame {
            display: block;

            width: 100%;
            height: calc(100vh - 190px);
            min-height: 650px;

            border: 0;
            background: #ffffff;
        }

        .footer {
            padding: 11px 20px;

            text-align: center;

            background: #f9fafb;
            border-top: 1px solid #e4e7ec;

            color: #667085;
            font-size: 12px;
        }

        @media (max-width: 700px) {
            .topbar {
                height: 64px;
                padding: 0 16px;
            }

            .secure {
                display: none;
            }

            .main {
                width: 100%;
                margin: 0;
            }

            .payment-card {
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
                min-height: 600px;
            }
        }
    </style>
</head>

<body>

<div class="page">

    <header class="topbar">

        <div class="brand">

            <div class="logo">
                D
            </div>

            <div class="brand-text">
                <strong>Development Work</strong>
                <span>Secure checkout</span>
            </div>

        </div>

        <div class="secure">
            🔒 Secure payment
        </div>

    </header>


    <main class="main">

        <section class="payment-card">

            <div class="payment-header">

                <div>
                    <h1>Complete your payment</h1>

                    <p>
                        Choose your preferred payment method below.
                    </p>
                </div>


                @if(isset($payment))

                    <div class="amount">

                        <span>Amount</span>

                        <strong>
                            {{ $payment->currency }}
                            {{ number_format((float) $payment->amount, 2) }}
                        </strong>

                    </div>

                @endif

            </div>


            <div class="frame-container">

                <iframe
                    class="pesapal-frame"
                    src="{{ $pesapalUrl }}"
                    title="Pesapal Secure Checkout"
                    allow="payment"
                    scrolling="auto"
                ></iframe>

            </div>


            <div class="footer">
                Payment processing is securely handled by Pesapal.
            </div>

        </section>

    </main>

</div>

</body>
</html>