<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use ShiftechAfrica\Pesapal\Data\BillingAddress;
use ShiftechAfrica\Pesapal\Data\IpnNotification;
use ShiftechAfrica\Pesapal\Data\OrderRequest;
use ShiftechAfrica\Pesapal\Data\TransactionStatus;
use ShiftechAfrica\Pesapal\Http\PesapalClient;

final class PesapalPaymentController
{
    /**
     * Create a Pesapal order.
     *
     * After Pesapal returns redirectUrl, we render the SAME
     * checkout Blade again, this time with $pesapalUrl.
     */
    public function checkout(
        Request $request,
        PesapalClient $pesapal
    ): View {
        $data = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:1',
            ],

            'email' => [
                'nullable',
                'email',
                'required_without:phone',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
                'required_without:email',
            ],

            'first_name' => [
                'nullable',
                'string',
                'max:80',
            ],

            'last_name' => [
                'nullable',
                'string',
                'max:80',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create local payment
        |--------------------------------------------------------------------------
        */

        $payment = Payment::query()->create([
            'merchant_reference' => 'BANK-'.Str::upper(Str::random(20)),
            'amount' => $data['amount'],
            'currency' => 'KES',
            'status' => 'PENDING',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create Pesapal order
        |--------------------------------------------------------------------------
        */

        $orderRequest = new OrderRequest(
            id: $payment->merchant_reference,
            amount: (float) $payment->amount,
            description: 'Development Work payment',

            billingAddress: new BillingAddress(
                emailAddress: $data['email'] ?? null,
                phoneNumber: $data['phone'] ?? null,
                countryCode: 'KE',
                firstName: $data['first_name'] ?? null,
                lastName: $data['last_name'] ?? null,
            ),
        );

        $order = $pesapal->submitOrder($orderRequest);

        /*
        |--------------------------------------------------------------------------
        | Save Pesapal result
        |--------------------------------------------------------------------------
        */

        $payment->update([
            'pesapal_tracking_id' => $order->orderTrackingId,
            'redirect_url' => $order->redirectUrl,
        ]);

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | We DO NOT redirect away to Pesapal.
        |
        | We DO NOT call:
        |
        |     view('payments.pesapal')
        |
        | We return the SAME existing checkout Blade and give it
        | the URL that should be shown inside the iframe.
        |
        */

        return view('payments.checkout', [
            'pesapalUrl' => $order->redirectUrl,
            'payment' => $payment->fresh(),
        ]);
    }

    /**
     * Customer callback from Pesapal.
     */
    public function callback(
        Request $request,
        PesapalClient $pesapal
    ): View {
        $notification = IpnNotification::fromArray(
            $request->query()
        );

        $transaction = $pesapal->getTransactionStatus(
            $notification->orderTrackingId
        );

        $payment = $this->reconcile(
            $notification,
            $transaction
        );

        return view('payments.result', [
            'payment' => $payment,
            'transaction' => $transaction,
        ]);
    }

    /**
     * Pesapal IPN endpoint.
     */
    public function ipn(
        Request $request,
        PesapalClient $pesapal
    ): JsonResponse {
        $notification = IpnNotification::fromArray(
            array_merge(
                $request->query(),
                $request->all()
            )
        );

        try {
            $transaction = $pesapal->getTransactionStatus(
                $notification->orderTrackingId
            );

            $this->reconcile(
                $notification,
                $transaction
            );

            return response()->json(
                $notification->acknowledgement(200)
            );
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(
                $notification->acknowledgement(500),
                500
            );
        }
    }

    /**
     * Reconcile Pesapal transaction with local payment.
     */
    private function reconcile(
        IpnNotification $notification,
        TransactionStatus $transaction
    ): Payment {
        return DB::transaction(
            function () use (
                $notification,
                $transaction
            ): Payment {
                /** @var Payment $payment */
                $payment = Payment::query()
                    ->where(
                        'merchant_reference',
                        $notification->orderMerchantReference
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                |--------------------------------------------------------------------------
                | Tracking ID validation
                |--------------------------------------------------------------------------
                */

                if (
                    $payment->pesapal_tracking_id !== null
                    && $payment->pesapal_tracking_id
                        !== $notification->orderTrackingId
                ) {
                    abort(
                        422,
                        'Pesapal tracking ID mismatch.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Merchant reference validation
                |--------------------------------------------------------------------------
                */

                if (
                    $transaction->merchantReference
                    !== $payment->merchant_reference
                ) {
                    abort(
                        422,
                        'Pesapal merchant reference mismatch.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Amount validation
                |--------------------------------------------------------------------------
                */

                if (
                    $transaction->amount === null
                    || number_format(
                        (float) $transaction->amount,
                        2,
                        '.',
                        ''
                    )
                    !== number_format(
                        (float) $payment->amount,
                        2,
                        '.',
                        ''
                    )
                ) {
                    abort(
                        422,
                        'Pesapal amount mismatch.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Currency validation
                |--------------------------------------------------------------------------
                */

                if (
                    strtoupper(
                        (string) $transaction->currency
                    )
                    !== strtoupper(
                        (string) $payment->currency
                    )
                ) {
                    abort(
                        422,
                        'Pesapal currency mismatch.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Update payment
                |--------------------------------------------------------------------------
                */

                $payment->fill([
                    'pesapal_tracking_id'
                        => $notification->orderTrackingId,

                    'status'
                        => $transaction->paymentStatus->value,

                    'payment_method'
                        => $transaction->paymentMethod,

                    'confirmation_code'
                        => $transaction->confirmationCode,

                    'payment_account'
                        => $transaction->paymentAccount,

                    'paid_at'
                        => $transaction->isCompleted()
                            ? ($payment->paid_at ?? now())
                            : $payment->paid_at,

                    'payload'
                        => $transaction->raw,
                ]);

                $payment->save();

                return $payment->refresh();
            }
        );
    }
}