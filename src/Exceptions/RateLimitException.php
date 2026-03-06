<?php

namespace ZyndPay\Exceptions;

class RateLimitException extends ZyndPayException
{
    public ?int $retryAfter;

    public function __construct(string $message = 'Rate limit exceeded', ?int $retryAfter = null, ?string $requestId = null)
    {
        parent::__construct($message, 429, $requestId);
        $this->retryAfter = $retryAfter;
    }
}
