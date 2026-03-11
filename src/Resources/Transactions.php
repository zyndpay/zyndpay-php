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
     * @param array $params Optional: page, limit, type, status, currency, chain, from_date, to_date, since
     * @return array{data: array, meta: array}
     */
    public function list(array $params = []): array
    {
        $res = $this->client->get('/transactions', $params);
        return ['data' => $res['data'], 'meta' => $res['meta'] ?? null];
    }

    /**
     * Export transactions as CSV.
     *
     * @param array $params Optional: type, status, currency, chain, from_date, to_date, since
     * @return mixed CSV content
     */
    public function export(array $params = [])
    {
        $res = $this->client->get('/transactions/export', $params);
        return $res['data'];
    }
}
