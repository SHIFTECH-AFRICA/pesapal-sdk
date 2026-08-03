<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Data;

use DateTimeImmutable;
use JsonSerializable;
use ShiftechAfrica\Pesapal\Enums\SubscriptionFrequency;
use ShiftechAfrica\Pesapal\Exceptions\ValidationException;

final readonly class SubscriptionDetails implements JsonSerializable
{
    public function __construct(
        public string $startDate,
        public string $endDate,
        public SubscriptionFrequency $frequency,
    ) {
        $start = DateTimeImmutable::createFromFormat('!d-m-Y', $startDate);
        $end = DateTimeImmutable::createFromFormat('!d-m-Y', $endDate);

        if ($start === false || $start->format('d-m-Y') !== $startDate) {
            throw new ValidationException('Subscription start date must use dd-MM-yyyy.');
        }

        if ($end === false || $end->format('d-m-Y') !== $endDate) {
            throw new ValidationException('Subscription end date must use dd-MM-yyyy.');
        }

        if ($end < $start) {
            throw new ValidationException('Subscription end date cannot be before the start date.');
        }
    }

    /** @return array{start_date: string, end_date: string, frequency: string} */
    public function jsonSerialize(): array
    {
        return [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'frequency' => $this->frequency->value,
        ];
    }
}
