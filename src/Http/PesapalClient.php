<?php

declare(strict_types=1);

namespace ShiftechAfrica\Pesapal\Http;

use DateTimeImmutable;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use ShiftechAfrica\Pesapal\Contracts\TokenStore;
use ShiftechAfrica\Pesapal\Data\CancellationResponse;
use ShiftechAfrica\Pesapal\Data\IpnEndpoint;
use ShiftechAfrica\Pesapal\Data\OrderRequest;
use ShiftechAfrica\Pesapal\Data\OrderResponse;
use ShiftechAfrica\Pesapal\Data\RefundResponse;
use ShiftechAfrica\Pesapal\Data\TransactionStatus;
use ShiftechAfrica\Pesapal\Enums\IpnMethod;
use ShiftechAfrica\Pesapal\Exceptions\ApiException;
use ShiftechAfrica\Pesapal\Exceptions\AuthenticationException;
use ShiftechAfrica\Pesapal\Exceptions\PaymentVerificationException;
use ShiftechAfrica\Pesapal\Exceptions\TransportException;
use ShiftechAfrica\Pesapal\Exceptions\ValidationException;
use ShiftechAfrica\Pesapal\PesapalConfig;
use ShiftechAfrica\Pesapal\Support\Amount;

final class PesapalClient
{
    public function __construct(
        private readonly ClientInterface $http,
        private readonly PesapalConfig $config,
        private readonly TokenStore $tokens,
    ) {
    }

    public function authenticate(bool $force = false): string
    {
        if (! $force && $this->config->tokenCacheEnabled) {
            $cached = $this->tokens->get($this->tokenCacheKey());

            if ($cached !== null) {
                return $cached;
            }
        }

        $response = $this->requestRaw('POST', '/Auth/RequestToken', [
            'json' => [
                'consumer_key' => $this->config->consumerKey,
                'consumer_secret' => $this->config->consumerSecret,
            ],
        ]);

        $data = $this->decode($response);
        $this->assertSuccessful($response, $data);
        $token = trim((string) ($data['token'] ?? ''));

        if ($token === '') {
            throw new AuthenticationException('Pesapal authentication succeeded without returning a token.');
        }

        if ($this->config->tokenCacheEnabled) {
            $ttl = $this->tokenTtl((string) ($data['expiryDate'] ?? ''));
            $this->tokens->put($this->tokenCacheKey(), $token, $ttl);
        }

        return $token;
    }

    public function registerIpn(string $url, IpnMethod $method = IpnMethod::Post): IpnEndpoint
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new ValidationException('IPN URL must be a valid absolute URL.');
        }

        $data = $this->authenticatedRequest('POST', '/URLSetup/RegisterIPN', [
            'json' => [
                'url' => $url,
                'ipn_notification_type' => $method->value,
            ],
        ]);

        return IpnEndpoint::fromArray($data);
    }

    /** @return list<IpnEndpoint> */
    public function listIpns(): array
    {
        $data = $this->authenticatedRequest('GET', '/URLSetup/GetIpnList');

        if (! array_is_list($data)) {
            throw new ApiException('Pesapal returned an invalid IPN list response.', response: $data);
        }

        return array_map(
            static fn (array $item): IpnEndpoint => IpnEndpoint::fromArray($item),
            array_filter($data, 'is_array'),
        );
    }

    public function submitOrder(OrderRequest $order): OrderResponse
    {
        $order = $order->withDefaults(
            callbackUrl: $this->config->callbackUrl,
            notificationId: $this->config->notificationId,
            currency: $this->config->currency,
            cancellationUrl: $this->config->cancellationUrl,
        );

        $data = $this->authenticatedRequest('POST', '/Transactions/SubmitOrderRequest', [
            'json' => $order->jsonSerialize(),
        ]);

        return OrderResponse::fromArray($data);
    }

    public function getTransactionStatus(string $orderTrackingId): TransactionStatus
    {
        if (trim($orderTrackingId) === '') {
            throw new ValidationException('Order tracking id is required.');
        }

        $data = $this->authenticatedRequest('GET', '/Transactions/GetTransactionStatus', [
            'query' => ['orderTrackingId' => $orderTrackingId],
        ]);

        return TransactionStatus::fromArray($data);
    }

    public function verifyPayment(
        string $orderTrackingId,
        string $merchantReference,
        int|float|string $amount,
        string $currency = 'KES',
        bool $requireCompleted = true,
    ): TransactionStatus {
        $transaction = $this->getTransactionStatus($orderTrackingId);
        $expectedAmount = new Amount($amount);

        if ($transaction->merchantReference !== $merchantReference) {
            throw new PaymentVerificationException('Pesapal merchant reference does not match the local payment.');
        }

        if ($transaction->amount === null || ! $expectedAmount->equals($transaction->amount)) {
            throw new PaymentVerificationException('Pesapal amount does not match the local payment.');
        }

        if (strtoupper((string) $transaction->currency) !== strtoupper($currency)) {
            throw new PaymentVerificationException('Pesapal currency does not match the local payment.');
        }

        if ($requireCompleted && ! $transaction->isCompleted()) {
            throw new PaymentVerificationException("Pesapal payment is {$transaction->paymentStatus->value}, not COMPLETED.");
        }

        return $transaction;
    }

    public function refund(
        string $confirmationCode,
        int|float|string $amount,
        string $username,
        string $remarks,
    ): RefundResponse {
        if (trim($confirmationCode) === '' || trim($username) === '' || trim($remarks) === '') {
            throw new ValidationException('Confirmation code, username, and remarks are required for a refund.');
        }

        $data = $this->authenticatedRequest('POST', '/Transactions/RefundRequest', [
            'json' => [
                'confirmation_code' => $confirmationCode,
                'amount' => (new Amount($amount))->apiValue(),
                'username' => $username,
                'remarks' => $remarks,
            ],
        ]);

        return RefundResponse::fromArray($data);
    }

    public function cancelOrder(string $orderTrackingId): CancellationResponse
    {
        if (trim($orderTrackingId) === '') {
            throw new ValidationException('Order tracking id is required.');
        }

        $data = $this->authenticatedRequest('POST', '/Transactions/CancelOrder', [
            'json' => ['order_tracking_id' => $orderTrackingId],
        ]);

        return CancellationResponse::fromArray($data);
    }

    /** @param array<string, mixed> $options @return array<string, mixed> */
    private function authenticatedRequest(string $method, string $path, array $options = []): array
    {
        $token = $this->authenticate();
        $response = $this->requestRaw($method, $path, $this->withBearer($options, $token));

        if ($response->getStatusCode() === 401) {
            $this->tokens->forget($this->tokenCacheKey());
            $token = $this->authenticate(force: true);
            $response = $this->requestRaw($method, $path, $this->withBearer($options, $token));
        }

        $data = $this->decode($response);
        $this->assertSuccessful($response, $data);

        return $data;
    }

    /** @param array<string, mixed> $options */
    private function requestRaw(string $method, string $path, array $options = []): ResponseInterface
    {
        $options['http_errors'] = false;
        $options['timeout'] = $this->config->timeout;
        $options['connect_timeout'] = $this->config->connectTimeout;
        $options['verify'] = $this->config->verifySsl;
        $options['headers'] = array_merge([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => $this->config->userAgent,
        ], is_array($options['headers'] ?? null) ? $options['headers'] : []);

        try {
            return $this->http->request($method, rtrim($this->config->baseUrl, '/').'/'.ltrim($path, '/'), $options);
        } catch (GuzzleException $exception) {
            throw new TransportException('Unable to communicate with Pesapal: '.$exception->getMessage(), previous: $exception);
        }
    }

    /** @param array<string, mixed> $options @return array<string, mixed> */
    private function withBearer(array $options, string $token): array
    {
        $options['headers'] = array_merge(
            is_array($options['headers'] ?? null) ? $options['headers'] : [],
            ['Authorization' => 'Bearer '.$token],
        );

        return $options;
    }

    /** @return array<string, mixed> */
    private function decode(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        if ($body === '') {
            return [];
        }

        $decoded = json_decode($body, true);

        if (! is_array($decoded)) {
            throw new ApiException(
                'Pesapal returned a non-JSON response.',
                httpStatus: $response->getStatusCode(),
                response: ['body' => $body],
            );
        }

        return $decoded;
    }

    /** @param array<string, mixed> $data */
    private function assertSuccessful(ResponseInterface $response, array $data): void
    {
        $httpStatus = $response->getStatusCode();
        $rawError = $data['error'] ?? null;
        $error = is_array($rawError) ? $rawError : null;
        $hasStructuredError = $error !== null && array_filter(
            $error,
            static fn (mixed $value): bool => $value !== null && $value !== '',
        ) !== [];
        $hasScalarError = $rawError !== null && ! is_array($rawError) && $rawError !== '' && $rawError !== 0 && $rawError !== '0';
        $bodyStatus = isset($data['status']) ? (string) $data['status'] : null;
        $bodyFailed = $bodyStatus !== null && $bodyStatus !== '' && ! str_starts_with($bodyStatus, '2');

        if ($httpStatus >= 200 && $httpStatus < 300 && ! $hasStructuredError && ! $hasScalarError && ! $bodyFailed) {
            return;
        }

        $message = (string) (
            ($error['message'] ?? null)
            ?? $data['message']
            ?? (is_scalar($rawError) ? $rawError : null)
            ?? "Pesapal API request failed with HTTP status {$httpStatus}."
        );

        throw new ApiException(
            message: $message,
            httpStatus: $httpStatus,
            apiCode: isset($error['code']) ? (string) $error['code'] : null,
            apiType: isset($error['type']) ? (string) $error['type'] : (isset($error['error_type']) ? (string) $error['error_type'] : null),
            response: $data,
        );
    }

    private function tokenCacheKey(): string
    {
        return implode('.', [
            $this->config->tokenCacheKey,
            $this->config->environment->value,
            substr(hash('sha256', $this->config->consumerKey), 0, 12),
        ]);
    }

    private function tokenTtl(string $expiryDate): int
    {
        $fallback = 270;

        if ($expiryDate === '') {
            return $fallback;
        }

        try {
            $expiry = new DateTimeImmutable($expiryDate);
            $ttl = $expiry->getTimestamp() - time() - max(0, $this->config->tokenSafetySeconds);

            return max(1, min($fallback, $ttl));
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
