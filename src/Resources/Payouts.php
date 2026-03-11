<?php

namespace ZyndPay\Resources;

use ZyndPay\HttpClient;

class Payouts
{
    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Initiate a payout.
     *
     * @param array $params {
     *   @type string $amount Amount in USDT (minimum 20), e.g. "100"
     *   @type string $destinationAddress TRON destination address
     *   @type string $currency Currency (default: USDT_TRC20)
     *   @type string $chain Chain (default: TRON)
     *   @type string $externalRef Your internal reference ID (optional)
     *   @type array $metadata Arbitrary metadata (optional)
     * }
     * @param string|null $idempotencyKey Unique key to prevent duplicates
     * @return array Payout object
     */
    public function create(array $params, ?string $idempotencyKey = null): array
    {
        $res = $this->client->post('/payout', $params, $idempotencyKey);
        return $res['data'];
    }

    /**
     * Get a payout by ID.
     */
    public function get(string $id): array
    {
        $res = $this->client->get("/payout/{$id}");
        return $res['data'];
    }

    /**
     * List payouts with optional filters.
     *
     * @param array $params Optional: page, limit, status
     * @return array{items: array, total: int}
     */
    public function list(array $params = []): array
    {
        $res = $this->client->get('/payout', $params);
        return $res['data'];
    }
}
