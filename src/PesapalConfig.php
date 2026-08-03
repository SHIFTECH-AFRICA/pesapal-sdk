<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal;

use ShiftechAfrica\Pesapal\Enums\Environment;
use ShiftechAfrica\Pesapal\Exceptions\ConfigurationException;

final readonly class PesapalConfig
{
    public function __construct(
        public Environment $environment,
        public string $consumerKey,
        public string $consumerSecret,
        public string $baseUrl,
        public ?string $notificationId = null,
        public ?string $callbackUrl = null,
        public ?string $cancellationUrl = null,
        public string $currency = 'KES',
        public float $timeout = 30,
        public float $connectTimeout = 10,
        public bool $verifySsl = true,
        public string $userAgent = 'shiftechafrica-pesapal-laravel-sdk/1.0',
        public bool $tokenCacheEnabled = true,
        public string $tokenCacheKey = 'pesapal.api.v3.access_token',
        public int $tokenSafetySeconds = 30,
    ) {
        if (trim($consumerKey) === '' || trim($consumerSecret) === '') {
            throw new ConfigurationException('PESAPAL_CONSUMER_KEY and PESAPAL_CONSUMER_SECRET are required.');
        }

        if (filter_var($baseUrl, FILTER_VALIDATE_URL) === false) {
            throw new ConfigurationException('Pesapal base URL is invalid.');
        }
    }

    /** @param array<string, mixed> $config */
    public static function fromArray(array $config): self
    {
        $environment = Environment::fromConfig((string) ($config['environment'] ?? 'sandbox'));
        $urls = is_array($config['urls'] ?? null) ? $config['urls'] : [];
        $http = is_array($config['http'] ?? null) ? $config['http'] : [];
        $tokenCache = is_array($config['token_cache'] ?? null) ? $config['token_cache'] : [];
        $baseUrl = (string) ($urls[$environment->value] ?? '');

        return new self(
            environment: $environment,
            consumerKey: (string) ($config['consumer_key'] ?? ''),
            consumerSecret: (string) ($config['consumer_secret'] ?? ''),
            baseUrl: rtrim($baseUrl, '/'),
            notificationId: self::nullableString($config['notification_id'] ?? null),
            callbackUrl: self::nullableString($config['callback_url'] ?? null),
            cancellationUrl: self::nullableString($config['cancellation_url'] ?? null),
            currency: strtoupper((string) ($config['currency'] ?? 'KES')),
            timeout: (float) ($http['timeout'] ?? 30),
            connectTimeout: (float) ($http['connect_timeout'] ?? 10),
            verifySsl: (bool) ($http['verify'] ?? true),
            userAgent: (string) ($http['user_agent'] ?? 'shiftechafrica-pesapal-laravel-sdk/1.0'),
            tokenCacheEnabled: (bool) ($tokenCache['enabled'] ?? true),
            tokenCacheKey: (string) ($tokenCache['key'] ?? 'pesapal.api.v3.access_token'),
            tokenSafetySeconds: (int) ($tokenCache['safety_seconds'] ?? 30),
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
