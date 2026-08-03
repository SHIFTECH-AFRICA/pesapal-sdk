<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Commands;

use Illuminate\Console\Command;
use ShiftechAfrica\Pesapal\Enums\IpnMethod;
use ShiftechAfrica\Pesapal\Http\PesapalClient;

final class RegisterIpnCommand extends Command
{
    protected $signature = 'pesapal:ipn:register {url? : Public IPN URL} {--method=POST : GET or POST}';

    protected $description = 'Register an Instant Payment Notification URL with Pesapal';

    public function handle(PesapalClient $pesapal): int
    {
        $url = (string) ($this->argument('url') ?: config('pesapal.ipn_url', ''));
        $method = IpnMethod::tryFrom(strtoupper((string) $this->option('method')));

        if ($url === '') {
            $this->error('Provide the IPN URL as an argument.');

            return self::FAILURE;
        }

        if ($method === null) {
            $this->error('The --method option must be GET or POST.');

            return self::FAILURE;
        }

        $endpoint = $pesapal->registerIpn($url, $method);

        $this->info('Pesapal IPN registered successfully.');
        $this->table(['IPN ID', 'URL', 'Method'], [[
            $endpoint->ipnId,
            $endpoint->url,
            $endpoint->notificationType ?? $method->value,
        ]]);

        return self::SUCCESS;
    }
}
