<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ShiftechAfrica\Pesapal\Data\BillingAddress;
use ShiftechAfrica\Pesapal\Data\OrderRequest;
use ShiftechAfrica\Pesapal\Exceptions\ValidationException;

final class OrderRequestTest extends TestCase
{
    public function test_it_builds_the_official_submit_order_payload(): void
    {
        $order = new OrderRequest(
            id: 'INV-1001',
            amount: '1250.50',
            description: 'Bank card payment',
            billingAddress: new BillingAddress(emailAddress: 'buyer@example.com'),
            callbackUrl: 'https://merchant.test/pesapal/callback',
            notificationId: 'fe078e53-78da-4a83-aa89-e7ded5c456e6',
        );

        $payload = $order->jsonSerialize();

        self::assertSame('INV-1001', $payload['id']);
        self::assertSame(1250.50, $payload['amount']);
        self::assertSame('KES', $payload['currency']);
        self::assertSame('buyer@example.com', $payload['billing_address']['email_address']);
    }

    public function test_it_requires_email_or_phone(): void
    {
        $this->expectException(ValidationException::class);

        new BillingAddress();
    }
}
