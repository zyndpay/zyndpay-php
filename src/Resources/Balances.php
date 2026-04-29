<?php

declare(strict_types=1);

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

    /**
     * Get balance for a specific currency.
     *
     * @param string $currency Currency code (e.g. 'USDT_TRC20')
     * @return array Balance object
     */
    public function getByCurrency(string $currency): array
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $currency)) {
            throw new \InvalidArgumentException('Invalid currency format');
        }
        $res = $this->client->get("/merchant/balances/$currency");
        return $res['data'];
    }
}
