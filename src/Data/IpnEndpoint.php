<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Data;

final readonly class IpnEndpoint
{
    public function __construct(
        public string $url,
        public string $ipnId,
        public ?string $createdDate,
        public ?string $notificationType,
        public ?string $statusDescription,
        public array $raw,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            url: (string) ($data['url'] ?? ''),
            ipnId: (string) ($data['ipn_id'] ?? ''),
            createdDate: isset($data['created_date']) ? (string) $data['created_date'] : null,
            notificationType: isset($data['ipn_notification_type_description']) ? (string) $data['ipn_notification_type_description'] : null,
            statusDescription: isset($data['ipn_status_decription']) ? (string) $data['ipn_status_decription'] : null,
            raw: $data,
        );
    }
}
