<?php

namespace ZyndPay\Exceptions;

class ZyndPayException extends \Exception
{
    public ?string $requestId;
    public int $statusCode;

    public function __construct(string $message = '', int $statusCode = 0, ?string $requestId = null)
    {
        parent::__construct($message, $statusCode);
        $this->statusCode = $statusCode;
        $this->requestId = $requestId;
    }
}
