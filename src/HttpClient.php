<?php

namespace ZyndPay;

use ZyndPay\Exceptions\AuthenticationException;
use ZyndPay\Exceptions\NotFoundException;
use ZyndPay\Exceptions\RateLimitException;
use ZyndPay\Exceptions\ValidationException;
use ZyndPay\Exceptions\ZyndPayException;

class HttpClient
{
    private const DEFAULT_BASE_URL = 'https://api.zyndpay.com/v1';
    private const DEFAULT_TIMEOUT = 30;
    private const DEFAULT_MAX_RETRIES = 2;
    private const RETRYABLE_STATUS_CODES = [408, 429, 500, 502, 503, 504];

    private string $apiKey;
    private string $baseUrl;
    private int $timeout;
    private int $maxRetries;

    public function __construct(
        string $apiKey,
        ?string $baseUrl = null,
        ?int $timeout = null,
        ?int $maxRetries = null,
    ) {
        if (empty($apiKey)) {
            throw new AuthenticationException('API key is required');
        }

        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl ?? self::DEFAULT_BASE_URL, '/');
        $this->timeout = $timeout ?? self::DEFAULT_TIMEOUT;
        $this->maxRetries = $maxRetries ?? self::DEFAULT_MAX_RETRIES;
    }

    public function get(string $path, array $params = []): array
    {
        return $this->request('GET', $path, null, $params);
    }

    public function post(string $path, ?array $body = null, ?string $idempotencyKey = null, array $params = []): array
    {
        return $this->request('POST', $path, $body, $params, $idempotencyKey);
    }

    public function delete(string $path): array
    {
        return $this->request('DELETE', $path);
    }

    private function request(
        string $method,
        string $path,
        ?array $body = null,
        array $params = [],
        ?string $idempotencyKey = null,
    ): array {
        // Filter null params
        $params = array_filter($params, fn($v) => $v !== null);
        $url = $this->baseUrl . $path;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $lastError = null;

        for ($attempt = 0; $attempt <= $this->maxRetries; $attempt++) {
            $ch = curl_init();

            $headers = [
                'X-Api-Key: ' . $this->apiKey,
                'Content-Type: application/json',
                'User-Agent: zyndpay-php/1.0.0',
            ];

            if ($idempotencyKey !== null) {
                $headers[] = 'Idempotency-Key: ' . $idempotencyKey;
            }

            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HEADER => true,
            ]);

            if ($body !== null && $method !== 'GET') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }

            $response = curl_exec($ch);

            if ($response === false) {
                $lastError = new ZyndPayException('cURL error: ' . curl_error($ch));
                curl_close($ch);
                if ($attempt < $this->maxRetries) {
                    usleep((int)(pow(2, $attempt) * 500000)); // exponential backoff
                }
                continue;
            }

            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $responseHeaders = substr($response, 0, $headerSize);
            $responseBody = substr($response, $headerSize);
            curl_close($ch);

            $requestId = $this->extractHeader($responseHeaders, 'x-request-id');

            if ($statusCode >= 200 && $statusCode < 300) {
                return json_decode($responseBody, true) ?: [];
            }

            if (!in_array($statusCode, self::RETRYABLE_STATUS_CODES)) {
                $parsed = json_decode($responseBody, true) ?: [];
                throw $this->createError($statusCode, $parsed, $requestId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int)($this->extractHeader($responseHeaders, 'retry-after') ?: '1');
                if ($attempt < $this->maxRetries) {
                    sleep($retryAfter);
                    continue;
                }
                throw new RateLimitException('Rate limit exceeded', $retryAfter, $requestId);
            }

            $lastError = new ZyndPayException(
                "Request failed with status $statusCode",
                $statusCode,
                $requestId,
            );

            if ($attempt < $this->maxRetries) {
                usleep((int)(pow(2, $attempt) * 500000));
            }
        }

        throw $lastError ?? new ZyndPayException('Request failed after retries');
    }

    private function createError(int $statusCode, array $body, ?string $requestId): ZyndPayException
    {
        $message = $body['message'] ?? $body['error'] ?? "Request failed with status $statusCode";

        return match ($statusCode) {
            401 => new AuthenticationException($message, $requestId),
            400 => new ValidationException($message, $requestId),
            404 => new NotFoundException($message, $requestId),
            429 => new RateLimitException($message, null, $requestId),
            default => new ZyndPayException($message, $statusCode, $requestId),
        };
    }

    private function extractHeader(string $headers, string $name): ?string
    {
        if (preg_match('/^' . preg_quote($name, '/') . ':\s*(.+)$/mi', $headers, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
}
