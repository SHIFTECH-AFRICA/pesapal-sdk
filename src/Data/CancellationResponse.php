<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Data;

final readonly class CancellationResponse
{
    public function __construct(
        public string $status,
        public ?string $message,
        public array $raw,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            status: (string) ($data['status'] ?? ''),
            message: isset($data['message']) ? (string) $data['message'] : null,
            raw: $data,
        );
    }

    public function wasCancelled(): bool
    {
        return $this->status === '200';
    }
}
