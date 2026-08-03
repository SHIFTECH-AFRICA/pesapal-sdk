<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use ShiftechAfrica\Pesapal\PesapalServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [PesapalServiceProvider::class];
    }
}
