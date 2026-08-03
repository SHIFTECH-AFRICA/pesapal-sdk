<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Data;

use ShiftechAfrica\Pesapal\Exceptions\ApiException;

final readonly class OrderResponse
{
    public function __construct(
        public string $orderTrackingId,
        public string $merchantReference,
        public string $redirectUrl,
        public string $status,
        public ?string $message = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $trackingId = (string) ($data['order_tracking_id'] ?? '');
        $reference = (string) ($data['merchant_reference'] ?? '');
        $redirectUrl = (string) ($data['redirect_url'] ?? '');

        if ($trackingId === '' || $reference === '' || $redirectUrl === '') {
            throw new ApiException('Pesapal returned an incomplete order response.', response: $data);
        }

        return new self(
            orderTrackingId: $trackingId,
            merchantReference: $reference,
            redirectUrl: $redirectUrl,
            status: (string) ($data['status'] ?? ''),
            message: isset($data['message']) ? (string) $data['message'] : null,
        );
    }
}
