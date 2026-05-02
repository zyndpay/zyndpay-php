<?php

declare(strict_types=1);

namespace ZyndPay\Resources;

use ZyndPay\HttpClient;

class Payins
{
    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new payin (payment request).
     *
     * @param array $params {
     *   @type string $amount Amount (USDT for crypto; multiples of 5 XOF for CARD / MOBILE_MONEY)
     *   @type string $externalRef Your internal order/reference ID (optional)
     *   @type int $expiresInSeconds Custom expiry in seconds, min 900 (optional)
     *   @type array $metadata Arbitrary metadata (optional)
     *   @type string $successUrl Redirect URL after confirmed payment (optional)
     *   @type string $cancelUrl Redirect URL if payment expires (optional)
     *   @type bool $sandbox Set to true for test mode (optional)
     *   @type string $paymentMethod "USDT_TRC20" (default), "CARD" or "MOBILE_MONEY" (optional)
     *   @type string $currency "USDT_TRC20", "XOF", "USD", or "EUR" (optional)
     *   @type string $customerName Customer full name (optional)
     *   @type string $customerEmail Customer email (optional)
     *   @type string $customerPhone Customer phone in E.164. Required for MOBILE_MONEY.
     *   @type string $operatorCode Override detected MoMo operator
     *          (ORANGE_BF | MOOV_BF | ORANGE_CI | MTN_CI | MOOV_CI |
     *          ORANGE_ML | MOOV_ML). Ignored for non-MOBILE_MONEY rails.
     * }
     * Response shape depends on the rail:
     *   USDT_TRC20:   address, qrCodeUrl, paymentUrl
     *   CARD:         hostedPaymentUrl
     *   MOBILE_MONEY: nextStep ("otp" | "approval"), operatorCode,
     *                 instruction ({fr, en})
     * @return array Payin object
     */
    public function create(array $params): array
    {
        $sandbox = $params['sandbox'] ?? false;
        unset($params['sandbox']);

        $query = $sandbox ? ['sandbox' => 'true'] : [];
        $res = $this->client->post('/payments', $params, null, $query);
        return $res['data'];
    }

    /**
     * Get a payin by ID.
     */
    public function get(string $id): array
    {
        $res = $this->client->get("/payments/{$id}");
        return $res['data'];
    }

    /**
     * List payins with optional filters.
     *
     * @param array $params Optional: page, limit, status
     * @return array{items: array, total: int}
     */
    public function list(array $params = []): array
    {
        $res = $this->client->get('/payments', $params);
        return $res['data'];
    }

    /**
     * Simulate payin confirmation (sandbox only). Triggers the
     * `payin.confirmed` webhook server-side.
     *
     * Returns an associative array with keys `message` and
     * `transactionId` — not a full payin. Call `get($id)` afterwards
     * to read the post-confirmation status, amount, and txHash.
     *
     * @return array{message: string, transactionId: string}
     */
    public function simulate(string $id): array
    {
        $res = $this->client->post("/sandbox/payments/{$id}/simulate");
        return $res['data'];
    }

    /**
     * Submit the OTP a customer received by SMS for a MOBILE_MONEY pay-in
     * created on an OTP-mode operator (Orange Money BF, Orange Money CI).
     * Call this after create() returns nextStep === "otp".
     *
     * @param string $id Transaction id from create()
     * @param string $otp 4-8 digit code the customer received
     * @return array {transactionId, status} — status is always
     *               AWAITING_PAYMENT; the final CONFIRMED lands via webhook.
     */
    public function submitOtp(string $id, string $otp): array
    {
        $res = $this->client->post("/payins/{$id}/submit-otp", ['otp' => $otp]);
        return $res['data'];
    }
}
