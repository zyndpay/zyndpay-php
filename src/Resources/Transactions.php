<?php

namespace ZyndPay\Resources;

use ZyndPay\HttpClient;

class Transactions
{
    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get a transaction by ID.
     */
    public function get(string $id): array
    {
        $res = $this->client->get("/transactions/{$id}");
        return $res['data'];
    }

    /**
     * List transactions with optional filters.
     *
     * @param array $params Optional: page, limit, type, status, from_date, to_date
     * @return array{data: array, meta: array}
     */
    public function list(array $params = []): array
    {
        $res = $this->client->get('/transactions', $params);
        return ['data' => $res['data'], 'meta' => $res['meta'] ?? null];
    }
}
