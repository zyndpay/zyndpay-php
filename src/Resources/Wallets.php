<?php

declare(strict_types=1);

namespace ZyndPay\Resources;

use ZyndPay\HttpClient;

class Wallets
{
    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    public function list(): array
    {
        $res = $this->client->get('/merchants/wallets');
        return $res['data'];
    }
}
