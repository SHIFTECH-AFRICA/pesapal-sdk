<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Exceptions;

class ApiException extends PesapalException
{
    /** @param array<string, mixed> $response */
    public function __construct(
        string $message,
        public readonly int $httpStatus = 0,
        public readonly ?string $apiCode = null,
        public readonly ?string $apiType = null,
        public readonly array $response = [],
    ) {
        parent::__construct($message, $httpStatus);
    }
}
