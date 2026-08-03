<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Facades;

use Illuminate\Support\Facades\Facade;
use ShiftechAfrica\Pesapal\Http\PesapalClient;

/** @see PesapalClient */
final class Pesapal extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PesapalClient::class;
    }
}
