<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Data;

use JsonSerializable;
use ShiftechAfrica\Pesapal\Exceptions\ValidationException;

final readonly class BillingAddress implements JsonSerializable
{
    public function __construct(
        public ?string $emailAddress = null,
        public ?string $phoneNumber = null,
        public ?string $countryCode = 'KE',
        public ?string $firstName = null,
        public ?string $middleName = null,
        public ?string $lastName = null,
        public ?string $line1 = null,
        public ?string $line2 = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $postalCode = null,
        public ?string $zipCode = null,
    ) {
        if ($this->blank($emailAddress) && $this->blank($phoneNumber)) {
            throw new ValidationException('Billing address requires an email address or phone number.');
        }

        if (! $this->blank($emailAddress) && filter_var($emailAddress, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidationException('Billing email address is invalid.');
        }

        if (! $this->blank($countryCode) && preg_match('/^[A-Za-z]{2}$/', (string) $countryCode) !== 1) {
            throw new ValidationException('Country code must be a two-letter ISO 3166-1 code.');
        }

        if (! $this->blank($state) && self::length((string) $state) > 3) {
            throw new ValidationException('Billing state may not exceed three characters.');
        }
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            emailAddress: self::nullableString($data['email_address'] ?? $data['emailAddress'] ?? null),
            phoneNumber: self::nullableString($data['phone_number'] ?? $data['phoneNumber'] ?? null),
            countryCode: self::nullableString($data['country_code'] ?? $data['countryCode'] ?? 'KE'),
            firstName: self::nullableString($data['first_name'] ?? $data['firstName'] ?? null),
            middleName: self::nullableString($data['middle_name'] ?? $data['middleName'] ?? null),
            lastName: self::nullableString($data['last_name'] ?? $data['lastName'] ?? null),
            line1: self::nullableString($data['line_1'] ?? $data['line1'] ?? null),
            line2: self::nullableString($data['line_2'] ?? $data['line2'] ?? null),
            city: self::nullableString($data['city'] ?? null),
            state: self::nullableString($data['state'] ?? null),
            postalCode: self::nullableString($data['postal_code'] ?? $data['postalCode'] ?? null),
            zipCode: self::nullableString($data['zip_code'] ?? $data['zipCode'] ?? null),
        );
    }

    /** @return array<string, string> */
    public function jsonSerialize(): array
    {
        return [
            'email_address' => $this->emailAddress ?? '',
            'phone_number' => $this->phoneNumber ?? '',
            'country_code' => strtoupper($this->countryCode ?? ''),
            'first_name' => $this->firstName ?? '',
            'middle_name' => $this->middleName ?? '',
            'last_name' => $this->lastName ?? '',
            'line_1' => $this->line1 ?? '',
            'line_2' => $this->line2 ?? '',
            'city' => $this->city ?? '',
            'state' => $this->state ?? '',
            'postal_code' => $this->postalCode ?? '',
            'zip_code' => $this->zipCode ?? '',
        ];
    }

    private function blank(?string $value): bool
    {
        return $value === null || trim($value) === '';
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
    private static function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }

}
