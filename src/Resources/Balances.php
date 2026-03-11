<?php

namespace ZyndPay\Resources;

use ZyndPay\HttpClient;

class Balances
{
    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get your USDT TRC20 balance.
     */
    public function get(): array
    {
        $res = $this->client->get('/merchant/balances/USDT_TRC20');
        return $res['data'];
    }

    /**
     * Get all balances.
     */
    public function getAll(): array
    {
        $res = $this->client->get('/merchant/balances');
        return $res['data'];
    }
}
