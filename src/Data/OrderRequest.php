<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Data;

use JsonSerializable;
use ShiftechAfrica\Pesapal\Exceptions\ValidationException;
use ShiftechAfrica\Pesapal\Support\Amount;

final readonly class OrderRequest implements JsonSerializable
{
    public Amount $amount;

    public function __construct(
        public string $id,
        int|float|string $amount,
        public string $description,
        public BillingAddress $billingAddress,
        public ?string $currency = null,
        public ?string $callbackUrl = null,
        public ?string $notificationId = null,
        public ?string $cancellationUrl = null,
        public ?string $accountNumber = null,
        public ?SubscriptionDetails $subscriptionDetails = null,
    ) {
        $id = trim($id);
        $description = trim($description);
        $currency = $currency === null ? null : strtoupper(trim($currency));

        if ($id === '' || self::length($id) > 50) {
            throw new ValidationException('Merchant order id is required and may not exceed 50 characters.');
        }

        if ($description === '' || self::length($description) > 100) {
            throw new ValidationException('Order description is required and may not exceed 100 characters.');
        }

        if ($currency !== null && preg_match('/^[A-Z]{3}$/', $currency) !== 1) {
            throw new ValidationException('Currency must be a three-letter ISO currency code.');
        }

        foreach (['callbackUrl' => $callbackUrl, 'cancellationUrl' => $cancellationUrl] as $field => $url) {
            if ($url !== null && filter_var($url, FILTER_VALIDATE_URL) === false) {
                throw new ValidationException("{$field} must be a valid absolute URL.");
            }
        }

        if ($subscriptionDetails !== null && ($accountNumber === null || trim($accountNumber) === '')) {
            throw new ValidationException('Recurring payments require an account number.');
        }

        $this->amount = new Amount($amount);
    }

    public function withDefaults(
        ?string $callbackUrl,
        ?string $notificationId,
        string $currency,
        ?string $cancellationUrl = null,
    ): self {
        return new self(
            id: $this->id,
            amount: $this->amount->decimal(),
            description: $this->description,
            billingAddress: $this->billingAddress,
            currency: $this->currency ?? strtoupper($currency),
            callbackUrl: $this->callbackUrl ?? $callbackUrl,
            notificationId: $this->notificationId ?? $notificationId,
            cancellationUrl: $this->cancellationUrl ?? $cancellationUrl,
            accountNumber: $this->accountNumber,
            subscriptionDetails: $this->subscriptionDetails,
        );
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        if ($this->callbackUrl === null || trim($this->callbackUrl) === '') {
            throw new ValidationException('A callback URL is required to submit a Pesapal order.');
        }

        if ($this->notificationId === null || trim($this->notificationId) === '') {
            throw new ValidationException('A Pesapal notification_id is required to submit an order.');
        }

        $payload = [
            'id' => $this->id,
            'currency' => strtoupper($this->currency ?? throw new ValidationException('Currency is required to submit a Pesapal order.')),
            'amount' => $this->amount->apiValue(),
            'description' => $this->description,
            'callback_url' => $this->callbackUrl,
            'notification_id' => $this->notificationId,
            'billing_address' => $this->billingAddress->jsonSerialize(),
        ];

        if ($this->cancellationUrl !== null && trim($this->cancellationUrl) !== '') {
            $payload['cancellation_url'] = $this->cancellationUrl;
        }

        if ($this->accountNumber !== null && trim($this->accountNumber) !== '') {
            $payload['account_number'] = $this->accountNumber;
        }

        if ($this->subscriptionDetails !== null) {
            $payload['subscription_details'] = $this->subscriptionDetails->jsonSerialize();
        }

        return $payload;
    }
    private static function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }

}
