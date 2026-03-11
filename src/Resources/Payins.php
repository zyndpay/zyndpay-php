<?php

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
     *   @type string $amount Amount in USDT (minimum 20), e.g. "100"
     *   @type string $externalRef Your internal order/reference ID (optional)
     *   @type int $expiresInSeconds Custom expiry in seconds, min 900 (optional)
     *   @type array $metadata Arbitrary metadata (optional)
     *   @type string $successUrl Redirect URL after confirmed payment (optional)
     *   @type string $cancelUrl Redirect URL if payment expires (optional)
     *   @type bool $sandbox Set to true for test mode (optional)
     * }
     * @return array Payin object
     */
    public function create(array $params): array
    {
        $sandbox = $params['sandbox'] ?? false;
        unset($params['sandbox']);

        $query = $sandbox ? ['sandbox' => 'true'] : [];
        $res = $this->client->post('/payin', $params, null, $query);
        return $res['data'];
    }

    /**
     * Get a payin by ID.
     */
    public function get(string $id): array
    {
        $res = $this->client->get("/payin/{$id}");
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
        $res = $this->client->get('/payin', $params);
        return $res['data'];
    }

    /**
     * Simulate payin confirmation (sandbox only).
     */
    public function simulate(string $id): array
    {
        $res = $this->client->post("/sandbox/payin/{$id}/simulate");
        return $res['data'];
    }
}
