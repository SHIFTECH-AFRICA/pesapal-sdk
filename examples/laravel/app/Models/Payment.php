<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Payment extends Model
{
    protected $fillable = [
        'merchant_reference',
        'pesapal_tracking_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'confirmation_code',
        'payment_account',
        'redirect_url',
        'paid_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'immutable_datetime',
            'payload' => 'array',
        ];
    }
}
