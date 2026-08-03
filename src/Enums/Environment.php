<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Enums;

use ShiftechAfrica\Pesapal\Exceptions\ConfigurationException;

enum Environment: string
{
    case Sandbox = 'sandbox';
    case Production = 'production';

    public static function fromConfig(string $value): self
    {
        return match (strtolower(trim($value))) {
            'sandbox', 'demo', 'test' => self::Sandbox,
            'production', 'live', 'prod' => self::Production,
            default => throw new ConfigurationException("Unsupported Pesapal environment [{$value}]."),
        };
    }
}
