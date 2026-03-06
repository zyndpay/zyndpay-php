<?php

namespace ZyndPay\Exceptions;

class AuthenticationException extends ZyndPayException
{
    public function __construct(string $message = 'Invalid API key', ?string $requestId = null)
    {
        parent::__construct($message, 401, $requestId);
    }
}
