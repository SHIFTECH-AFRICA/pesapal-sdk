<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Data;

use ShiftechAfrica\Pesapal\Enums\PaymentStatus;

final readonly class TransactionStatus
{
    /** @param array<string, mixed>|null $subscriptionTransactionInfo */
    public function __construct(
        public PaymentStatus $paymentStatus,
        public ?string $paymentMethod,
        public ?float $amount,
        public ?string $currency,
        public ?string $merchantReference,
        public ?string $confirmationCode,
        public ?string $paymentAccount,
        public ?string $description,
        public ?string $message,
        public ?string $createdDate,
        public ?int $statusCode,
        public ?string $callbackUrl,
        public ?array $subscriptionTransactionInfo,
        public array $raw,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            paymentStatus: PaymentStatus::fromApi(isset($data['payment_status_description']) ? (string) $data['payment_status_description'] : null),
            paymentMethod: self::nullableString($data['payment_method'] ?? null),
            amount: isset($data['amount']) && is_numeric($data['amount']) ? (float) $data['amount'] : null,
            currency: self::nullableString($data['currency'] ?? null),
            merchantReference: self::nullableString($data['merchant_reference'] ?? null),
            confirmationCode: self::nullableString($data['confirmation_code'] ?? null),
            paymentAccount: self::nullableString($data['payment_account'] ?? null),
            description: self::nullableString($data['description'] ?? null),
            message: self::nullableString($data['message'] ?? null),
            createdDate: self::nullableString($data['created_date'] ?? null),
            statusCode: isset($data['status_code']) && is_numeric($data['status_code']) ? (int) $data['status_code'] : null,
            callbackUrl: self::nullableString($data['call_back_url'] ?? null),
            subscriptionTransactionInfo: is_array($data['subscription_transaction_info'] ?? null) ? $data['subscription_transaction_info'] : null,
            raw: $data,
        );
    }

    public function isCompleted(): bool
    {
        return $this->paymentStatus->isPaid();
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
