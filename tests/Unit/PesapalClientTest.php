<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ShiftechAfrica\Pesapal\Data\BillingAddress;
use ShiftechAfrica\Pesapal\Data\OrderRequest;
use ShiftechAfrica\Pesapal\Enums\Environment;
use ShiftechAfrica\Pesapal\Http\PesapalClient;
use ShiftechAfrica\Pesapal\PesapalConfig;
use ShiftechAfrica\Pesapal\Support\ArrayTokenStore;

final class PesapalClientTest extends TestCase
{
    public function test_it_authenticates_and_submits_an_order(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'token' => 'jwt-token',
                'expiryDate' => gmdate(DATE_ATOM, time() + 300),
                'status' => '200',
                'error' => null,
            ], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode([
                'order_tracking_id' => 'b945e4af-80a5-4ec1-8706-e03f8332fb04',
                'merchant_reference' => 'INV-1001',
                'redirect_url' => 'https://cybqa.pesapal.com/checkout',
                'status' => '200',
                'error' => null,
            ], JSON_THROW_ON_ERROR)),
        ]);

        $client = new PesapalClient(
            new Client(['handler' => HandlerStack::create($mock)]),
            new PesapalConfig(
                environment: Environment::Sandbox,
                consumerKey: 'key',
                consumerSecret: 'secret',
                baseUrl: 'https://cybqa.pesapal.com/pesapalv3/api',
                notificationId: 'fe078e53-78da-4a83-aa89-e7ded5c456e6',
                callbackUrl: 'https://merchant.test/pesapal/callback',
            ),
            new ArrayTokenStore(),
        );

        $response = $client->submitOrder(new OrderRequest(
            id: 'INV-1001',
            amount: 100,
            description: 'Test card payment',
            billingAddress: new BillingAddress(emailAddress: 'buyer@example.com'),
        ));

        self::assertSame('INV-1001', $response->merchantReference);
        self::assertSame('https://cybqa.pesapal.com/checkout', $response->redirectUrl);
    }
}
