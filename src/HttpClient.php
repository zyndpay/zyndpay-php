<?php

declare(strict_types=1);

namespace ZyndPay;

use ZyndPay\Exceptions\AuthenticationException;
use ZyndPay\Exceptions\ConflictException;
use ZyndPay\Exceptions\NotFoundException;
use ZyndPay\Exceptions\RateLimitException;
use ZyndPay\Exceptions\ValidationException;
use ZyndPay\Exceptions\ZyndPayException;

class HttpClient
{
    private const DEFAULT_BASE_URL = 'https://api.zyndpay.io/v1';
    private const DEFAULT_TIMEOUT = 30;
    private const DEFAULT_MAX_RETRIES = 2;
    private const RETRYABLE_STATUS_CODES = [408, 429, 500, 502, 503, 504];
    private const SDK_VERSION = '1.5.0';

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

    /**
     * Fetch a raw text response (e.g. CSV export, template download).
     */
    public function getRaw(string $path, array $params = []): string
    {
        $params = array_filter($params, fn($v) => $v !== null);
        $url = $this->baseUrl . $path;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'X-Api-Key: ' . $this->apiKey,
                'User-Agent: zyndpay-php/' . self::SDK_VERSION,
                'X-ZyndPay-Api-Version: 2024-01-01',
            ],
            CURLOPT_HEADER => true,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            throw new ZyndPayException('cURL error: ' . $error);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $responseBody = substr($response, $headerSize);
        $responseHeaders = substr($response, 0, $headerSize);

        if ($statusCode >= 200 && $statusCode < 300) {
            return $responseBody;
        }

        $requestId = $this->extractHeader($responseHeaders, 'x-request-id');
        try {
            $parsed = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $parsed = [];
        }
        throw $this->createError($statusCode, $parsed, $requestId);
    }

    public function patch(string $path, ?array $body = null): array
    {
        return $this->request('PATCH', $path, $body);
    }

    public function delete(string $path): array
    {
        return $this->request('DELETE', $path);
    }

    /**
     * Upload a file via multipart form data.
     *
     * @param string $path API path
     * @param string $filePath Local file path
     * @param string $fieldName Form field name (default: 'file')
     * @return array Parsed response
     */
    public function postFile(string $path, string $filePath, string $fieldName = 'file'): array
    {
        $url = $this->baseUrl . $path;
        $ch = curl_init();

        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        $postFields = [
            $fieldName => new \CURLFile($filePath, $mimeType, basename($filePath)),
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'X-Api-Key: ' . $this->apiKey,
                'User-Agent: zyndpay-php/' . self::SDK_VERSION,
                'X-ZyndPay-Api-Version: 2024-01-01',
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HEADER => true,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            throw new ZyndPayException('cURL error: ' . $error);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $responseBody = substr($response, $headerSize);
        $responseHeaders = substr($response, 0, $headerSize);

        $requestId = $this->extractHeader($responseHeaders, 'x-request-id');

        if ($statusCode >= 200 && $statusCode < 300) {
            return json_decode($responseBody, true) ?: [];
        }

        try {
            $parsed = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $parsed = [];
        }
        throw $this->createError($statusCode, $parsed, $requestId);
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
                'User-Agent: zyndpay-php/' . self::SDK_VERSION,
                'X-ZyndPay-Api-Version: 2024-01-01',
            ];

            if ($idempotencyKey !== null) {
                $idempotencyKey = str_replace(["\r", "\n"], '', $idempotencyKey);
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
                if ($attempt < $this->maxRetries) {
                    usleep((int)(pow(2, $attempt) * 500000)); // exponential backoff
                }
                continue;
            }

            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $responseHeaders = substr($response, 0, $headerSize);
            $responseBody = substr($response, $headerSize);

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

    private function createError(int $statusCode, array $body, ?string $requestId = null): ZyndPayException
    {
        $errorObj = $body['error'] ?? null;
        if (is_array($errorObj)) {
            $message = $errorObj['message'] ?? "Request failed with status $statusCode";
            $code = $errorObj['code'] ?? null;
            $details = $errorObj['details'] ?? null;
        } else {
            $message = $body['message'] ?? (is_string($errorObj) ? $errorObj : "Request failed with status $statusCode");
            $code = null;
            $details = null;
        }

        return match (true) {
            $statusCode === 400, $statusCode === 422 => new ValidationException($message, $requestId, $code ?? 'VALIDATION_ERROR', $details),
            $statusCode === 401 => new AuthenticationException($message, $requestId, $code ?? 'UNAUTHORIZED'),
            $statusCode === 403 => new AuthenticationException($message, $requestId, $code ?? 'FORBIDDEN'),
            $statusCode === 404 => new NotFoundException($message, $requestId, $code ?? 'NOT_FOUND'),
            $statusCode === 409 => new ConflictException($message, $requestId, $code ?? 'CONFLICT'),
            $statusCode === 429 => new RateLimitException($message, $this->parseRetryAfter($body), $requestId, $code ?? 'RATE_LIMIT_EXCEEDED'),
            default => new ZyndPayException($message, $statusCode, $requestId, $code),
        };
    }

    private function parseRetryAfter(array $body): ?int
    {
        if (isset($body['retryAfter'])) {
            return (int) $body['retryAfter'];
        }
        if (isset($body['retry_after'])) {
            return (int) $body['retry_after'];
        }
        return null;
    }

    private function extractHeader(string $headers, string $name): ?string
    {
        if (preg_match('/^' . preg_quote($name, '/') . ':\s*(.+)$/mi', $headers, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
}
