<?php

namespace ZyndPay\Exceptions;

class NotFoundException extends ZyndPayException
{
    public function __construct(string $message = 'Resource not found', ?string $requestId = null)
    {
        parent::__construct($message, 404, $requestId);
    }
}
