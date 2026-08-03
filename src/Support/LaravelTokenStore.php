<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Support;

use Illuminate\Contracts\Cache\Repository;
use ShiftechAfrica\Pesapal\Contracts\TokenStore;

final readonly class LaravelTokenStore implements TokenStore
{
    public function __construct(private Repository $cache)
    {
    }

    public function get(string $key): ?string
    {
        $value = $this->cache->get($key);

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function put(string $key, string $token, int $ttlSeconds): void
    {
        $this->cache->put($key, $token, max(1, $ttlSeconds));
    }

    public function forget(string $key): void
    {
        $this->cache->forget($key);
    }
}
