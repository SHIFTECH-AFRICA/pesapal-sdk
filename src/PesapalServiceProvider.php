<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal;

use GuzzleHttp\Client;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\ServiceProvider;
use ShiftechAfrica\Pesapal\Commands\ListIpnsCommand;
use ShiftechAfrica\Pesapal\Commands\RegisterIpnCommand;
use ShiftechAfrica\Pesapal\Contracts\TokenStore;
use ShiftechAfrica\Pesapal\Http\PesapalClient;
use ShiftechAfrica\Pesapal\Support\LaravelTokenStore;

final class PesapalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/pesapal.php', 'pesapal');

        $this->app->singleton(PesapalConfig::class, fn (): PesapalConfig => PesapalConfig::fromArray(
            (array) config('pesapal', []),
        ));

        $this->app->singleton(TokenStore::class, fn ($app): TokenStore => new LaravelTokenStore(
            $app->make(CacheRepository::class),
        ));

        $this->app->singleton(PesapalClient::class, fn ($app): PesapalClient => new PesapalClient(
            http: new Client(),
            config: $app->make(PesapalConfig::class),
            tokens: $app->make(TokenStore::class),
        ));

        $this->app->alias(PesapalClient::class, 'pesapal');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/pesapal.php' => config_path('pesapal.php'),
        ], 'pesapal-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                RegisterIpnCommand::class,
                ListIpnsCommand::class,
            ]);
        }
    }
}
