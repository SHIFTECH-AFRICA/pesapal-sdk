<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Data;

use ShiftechAfrica\Pesapal\Exceptions\ValidationException;

final readonly class IpnNotification
{
    public function __construct(
        public string $orderTrackingId,
        public string $orderMerchantReference,
        public string $orderNotificationType,
    ) {
        if (trim($orderTrackingId) === '' || trim($orderMerchantReference) === '' || trim($orderNotificationType) === '') {
            throw new ValidationException('Pesapal notification is missing required parameters.');
        }
    }

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        return new self(
            orderTrackingId: self::pick($payload, ['OrderTrackingId', 'orderTrackingId', 'order_tracking_id']),
            orderMerchantReference: self::pick($payload, ['OrderMerchantReference', 'orderMerchantReference', 'order_merchant_reference']),
            orderNotificationType: strtoupper(self::pick($payload, ['OrderNotificationType', 'orderNotificationType', 'order_notification_type'])),
        );
    }

    /** @return array{orderNotificationType: string, orderTrackingId: string, orderMerchantReference: string, status: int} */
    public function acknowledgement(int $status = 200): array
    {
        return [
            'orderNotificationType' => $this->orderNotificationType,
            'orderTrackingId' => $this->orderTrackingId,
            'orderMerchantReference' => $this->orderMerchantReference,
            'status' => $status,
        ];
    }

    /** @param array<string, mixed> $payload @param list<string> $keys */
    private static function pick(array $payload, array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($payload[$key]) && trim((string) $payload[$key]) !== '') {
                return trim((string) $payload[$key]);
            }
        }

        return '';
    }
}
