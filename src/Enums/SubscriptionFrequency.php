<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Enums;

enum SubscriptionFrequency: string
{
    case Daily = 'DAILY';
    case Weekly = 'WEEKLY';
    case Monthly = 'MONTHLY';
    case Yearly = 'YEARLY';
}
