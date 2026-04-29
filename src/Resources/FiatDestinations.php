<?php

declare(strict_types=1);

namespace ZyndPay\Resources;

use ZyndPay\HttpClient;

class FiatDestinations
{
    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    public function list(): array
    {
        $res = $this->client->get('/merchants/fiat-destinations');
        return $res['data'];
    }

    public function create(array $params): array
    {
        $res = $this->client->post('/merchants/fiat-destinations', $params);
        return $res['data'];
    }

    public function update(string $id, array $params): array
    {
        $res = $this->client->patch("/merchants/fiat-destinations/{$id}", $params);
        return $res['data'];
    }

    public function delete(string $id): void
    {
        $this->client->delete("/merchants/fiat-destinations/{$id}");
    }
}
