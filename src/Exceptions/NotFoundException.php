<?php

declare(strict_types=1);

namespace ZyndPay\Exceptions;

class NotFoundException extends ZyndPayException
{
    public function __construct(string $message, ?string $requestId = null, string $errorCode = 'NOT_FOUND')
    {
        parent::__construct($message, 404, $requestId, $errorCode);
    }
}
