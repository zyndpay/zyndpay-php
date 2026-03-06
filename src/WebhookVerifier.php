<?php

namespace ZyndPay;

class WebhookVerifier
{
    public const HEADER_NAME = 'x-zyndpay-signature';

    private string $signingSecret;

    public function __construct(string $signingSecret)
    {
        $this->signingSecret = $signingSecret;
    }

    /**
     * Verify a webhook signature and parse the event payload.
     *
     * @param string $payload The raw request body
     * @param string $signature The value of the X-ZyndPay-Signature header
     * @param int $toleranceSeconds Max age of the event in seconds (default: 300)
     * @return array The parsed webhook event
     * @throws \InvalidArgumentException If the signature is invalid or the event is too old
     *
     * Example:
     *   $event = $zyndpay->webhooks->verify($payload, $_SERVER['HTTP_X_ZYNDPAY_SIGNATURE']);
     *   echo $event['type']; // "payin.confirmed"
     */
    public function verify(string $payload, string $signature, int $toleranceSeconds = 300): array
    {
        if (empty($signature)) {
            throw new \InvalidArgumentException('Missing webhook signature header');
        }

        $parts = explode(',', $signature);
        $tPart = null;
        $v1Part = null;

        foreach ($parts as $part) {
            if (str_starts_with($part, 't=')) {
                $tPart = $part;
            } elseif (str_starts_with($part, 'v1=')) {
                $v1Part = $part;
            }
        }

        if ($tPart === null || $v1Part === null) {
            throw new \InvalidArgumentException('Invalid webhook signature format — expected "t=<timestamp>,v1=<hmac>"');
        }

        $timestamp = (int)str_replace('t=', '', $tPart);
        $receivedHmac = str_replace('v1=', '', $v1Part);

        $now = time();
        if (abs($now - $timestamp) > $toleranceSeconds) {
            throw new \InvalidArgumentException(
                sprintf('Webhook timestamp too old (%ds > %ds tolerance)', abs($now - $timestamp), $toleranceSeconds)
            );
        }

        $signedPayload = "{$timestamp}.{$payload}";
        $expectedHmac = hash_hmac('sha256', $signedPayload, $this->signingSecret);

        if (!hash_equals($expectedHmac, $receivedHmac)) {
            throw new \InvalidArgumentException('Webhook signature verification failed');
        }

        return json_decode($payload, true);
    }
}
