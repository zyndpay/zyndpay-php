<?php

declare(strict_types=1);

namespace ZyndPay\Exceptions;

class RateLimitException extends ZyndPayException
{
    public readonly ?int $retryAfter;

    public function __construct(string $message, ?int $retryAfter = null, ?string $requestId = null, string $errorCode = 'RATE_LIMIT_EXCEEDED')
    {
        parent::__construct($message, 429, $requestId, $errorCode);
        $this->retryAfter = $retryAfter;
    }
}
