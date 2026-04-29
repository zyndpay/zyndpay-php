<?php

declare(strict_types=1);

namespace ZyndPay\Exceptions;

class AuthenticationException extends ZyndPayException
{
    public function __construct(string $message, ?string $requestId = null, string $errorCode = 'UNAUTHORIZED')
    {
        parent::__construct($message, 401, $requestId, $errorCode);
    }
}
