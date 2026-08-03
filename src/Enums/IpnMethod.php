<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Enums;

enum IpnMethod: string
{
    case Get = 'GET';
    case Post = 'POST';
}
