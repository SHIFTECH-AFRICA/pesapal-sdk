<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Support;

use ShiftechAfrica\Pesapal\Contracts\TokenStore;

final class ArrayTokenStore implements TokenStore
{
    /** @var array<string, array{token: string, expires_at: int}> */
    private array $tokens = [];

    public function get(string $key): ?string
    {
        $item = $this->tokens[$key] ?? null;

        if ($item === null || $item['expires_at'] <= time()) {
            unset($this->tokens[$key]);

            return null;
        }

        return $item['token'];
    }

    public function put(string $key, string $token, int $ttlSeconds): void
    {
        $this->tokens[$key] = [
            'token' => $token,
            'expires_at' => time() + max(1, $ttlSeconds),
        ];
    }

    public function forget(string $key): void
    {
        unset($this->tokens[$key]);
    }
}
