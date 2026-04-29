<?php

declare(strict_types=1);

namespace ZyndPay\Resources;

use ZyndPay\HttpClient;

class Conversions
{
    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    public function convertBetweenWallets(array $params, ?string $idempotencyKey = null): array
    {
        $res = $this->client->post('/conversions/wallet', $params, $idempotencyKey);
        return $res['data'];
    }

    public function get(string $id): array
    {
        $res = $this->client->get("/conversions/{$id}");
        return $res['data'];
    }

    public function list(array $params = []): array
    {
        $res = $this->client->get('/conversions', $params);
        return $res['data'];
    }
}
