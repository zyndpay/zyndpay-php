<?php

declare(strict_types=1);

namespace ZyndPay\Exceptions;

class ConflictException extends ZyndPayException
{
    public function __construct(string $message, ?string $requestId = null, string $errorCode = 'CONFLICT')
    {
        parent::__construct($message, 409, $requestId, $errorCode);
    }
}
