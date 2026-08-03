<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Enums;

enum PaymentStatus: string
{
    case Invalid = 'INVALID';
    case Pending = 'PENDING';
    case Completed = 'COMPLETED';
    case Failed = 'FAILED';
    case Reversed = 'REVERSED';
    case Unknown = 'UNKNOWN';

    public static function fromApi(?string $value): self
    {
        return self::tryFrom(strtoupper(trim((string) $value))) ?? self::Unknown;
    }

    public function isPaid(): bool
    {
        return $this === self::Completed;
    }
}
