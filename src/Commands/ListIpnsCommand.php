<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Commands;

use Illuminate\Console\Command;
use ShiftechAfrica\Pesapal\Http\PesapalClient;

final class ListIpnsCommand extends Command
{
    protected $signature = 'pesapal:ipn:list';

    protected $description = 'List IPN endpoints registered with Pesapal';

    public function handle(PesapalClient $pesapal): int
    {
        $rows = array_map(static fn ($endpoint): array => [
            $endpoint->ipnId,
            $endpoint->url,
            $endpoint->notificationType ?? '-',
            $endpoint->statusDescription ?? '-',
        ], $pesapal->listIpns());

        $this->table(['IPN ID', 'URL', 'Method', 'Status'], $rows);

        return self::SUCCESS;
    }
}
