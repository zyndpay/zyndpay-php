<?php

declare(strict_types=1);

namespace ZyndPay\Exceptions;

class ValidationException extends ZyndPayException
{
    public readonly ?array $details;

    public function __construct(string $message, ?string $requestId = null, string $errorCode = 'VALIDATION_ERROR', ?array $details = null)
    {
        parent::__construct($message, 400, $requestId, $errorCode);
        $this->details = $details;
    }
}
