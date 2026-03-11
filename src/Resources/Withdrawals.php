<?php

namespace ZyndPay\Resources;

use ZyndPay\HttpClient;

class Withdrawals
{
    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Request a withdrawal from your balance.
     *
     * @param array $params {
     *   @type string $amount Amount to withdraw in USDT, e.g. "100.00"
     *   @type string $whitelistAddressId ID of whitelisted address (optional)
     * }
     * @param string|null $idempotencyKey Unique key to prevent duplicates
     * @return array Withdrawal object
     */
    public function create(array $params, ?string $idempotencyKey = null): array
    {
        $res = $this->client->post('/withdrawals', $params, $idempotencyKey);
        return $res['data'];
    }

    /**
     * Get a withdrawal by ID.
     */
    public function get(string $id): array
    {
        $res = $this->client->get("/withdrawals/{$id}");
        return $res['data'];
    }

    /**
     * List withdrawals with optional filters.
     *
     * @param array $params Optional: page, limit, status
     * @return array{items: array, total: int}
     */
    public function list(array $params = []): array
    {
        $res = $this->client->get('/withdrawals', $params);
        return $res['data'];
    }

    /**
     * Cancel a pending withdrawal.
     */
    public function cancel(string $id): void
    {
        $this->client->delete("/withdrawals/{$id}");
    }
}
