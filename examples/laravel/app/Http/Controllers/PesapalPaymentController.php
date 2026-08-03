<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
    public function checkout(Request $request, PesapalClient $pesapal): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'email' => ['required_without:phone', 'nullable', 'email'],
            'phone' => ['required_without:email', 'nullable', 'string', 'max:30'],
            'first_name' => ['nullable', 'string', 'max:80'],
            'last_name' => ['nullable', 'string', 'max:80'],
        ]);

        $payment = Payment::query()->create([
            'merchant_reference' => 'BANK-'.Str::upper(Str::random(20)),
            'amount' => $data['amount'],
            'currency' => 'KES',
            'status' => 'PENDING',
        ]);

        $order = $pesapal->submitOrder(new OrderRequest(
            id: $payment->merchant_reference,
            amount: $payment->amount,
            description: 'Bank card payment',
            billingAddress: new BillingAddress(
                emailAddress: $data['email'] ?? null,
                phoneNumber: $data['phone'] ?? null,
                countryCode: 'KE',
                firstName: $data['first_name'] ?? null,
                lastName: $data['last_name'] ?? null,
            ),
        ));

        $payment->update([
            'pesapal_tracking_id' => $order->orderTrackingId,
            'redirect_url' => $order->redirectUrl,
        ]);

        return redirect()->away($order->redirectUrl);
    }

    public function callback(Request $request, PesapalClient $pesapal): View
    {
        $notification = IpnNotification::fromArray($request->query());
        $transaction = $pesapal->getTransactionStatus($notification->orderTrackingId);
        $payment = $this->reconcile($notification, $transaction);

        return view('payments.result', compact('payment', 'transaction'));
    }

    public function ipn(Request $request, PesapalClient $pesapal): JsonResponse
    {
        $notification = IpnNotification::fromArray(array_merge($request->query(), $request->all()));

        try {
            $transaction = $pesapal->getTransactionStatus($notification->orderTrackingId);
            $this->reconcile($notification, $transaction);

            return response()->json($notification->acknowledgement(200));
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json($notification->acknowledgement(500), 500);
        }
    }

    private function reconcile(IpnNotification $notification, TransactionStatus $transaction): Payment
    {
        return DB::transaction(function () use ($notification, $transaction): Payment {
            /** @var Payment $payment */
            $payment = Payment::query()
                ->where('merchant_reference', $notification->orderMerchantReference)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->pesapal_tracking_id !== null && $payment->pesapal_tracking_id !== $notification->orderTrackingId) {
                abort(422, 'Pesapal tracking id mismatch.');
            }

            if ($transaction->merchantReference !== $payment->merchant_reference) {
                abort(422, 'Pesapal merchant reference mismatch.');
            }

            if ($transaction->amount === null || number_format($transaction->amount, 2, '.', '') !== $payment->amount) {
                abort(422, 'Pesapal amount mismatch.');
            }

            if (strtoupper((string) $transaction->currency) !== strtoupper($payment->currency)) {
                abort(422, 'Pesapal currency mismatch.');
            }

            $payment->fill([
                'pesapal_tracking_id' => $notification->orderTrackingId,
                'status' => $transaction->paymentStatus->value,
                'payment_method' => $transaction->paymentMethod,
                'confirmation_code' => $transaction->confirmationCode,
                'payment_account' => $transaction->paymentAccount,
                'paid_at' => $transaction->isCompleted() ? ($payment->paid_at ?? now()) : $payment->paid_at,
                'payload' => $transaction->raw,
            ])->save();

            return $payment->refresh();
        });
    }
}
