<?php

namespace ZyndPay\Exceptions;

class ValidationException extends ZyndPayException
{
    public function __construct(string $message = 'Validation failed', ?string $requestId = null)
    {
        parent::__construct($message, 400, $requestId);
    }
}
