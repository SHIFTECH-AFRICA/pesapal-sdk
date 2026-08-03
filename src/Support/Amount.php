<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Support;

use ShiftechAfrica\Pesapal\Exceptions\ValidationException;

final readonly class Amount
{
    private string $decimal;

    public function __construct(int|float|string $value)
    {
        if (! is_numeric($value)) {
            throw new ValidationException('Amount must be numeric.');
        }

        $number = (float) $value;

        if (! is_finite($number) || $number <= 0) {
            throw new ValidationException('Amount must be greater than zero.');
        }

        $this->decimal = number_format($number, 2, '.', '');
    }

    public function decimal(): string
    {
        return $this->decimal;
    }

    public function apiValue(): float
    {
        return (float) $this->decimal;
    }

    public function equals(int|float|string $other): bool
    {
        return $this->decimal === (new self($other))->decimal();
    }
}
