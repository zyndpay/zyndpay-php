<?php

declare(strict_types=1);

namespace ZyndPay\Exceptions;

class ZyndPayException extends \Exception
{
    public readonly int $statusCode;
    public readonly ?string $requestId;
    public readonly ?string $errorCode;
    public readonly mixed $rawBody;

    public function __construct(
        string $message,
        int $statusCode = 0,
        ?string $requestId = null,
        ?string $errorCode = null,
        mixed $rawBody = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
        $this->requestId = $requestId;
        $this->errorCode = $errorCode;
        $this->rawBody = $rawBody;
    }
}
