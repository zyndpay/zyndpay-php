<?php

namespace ZyndPay;

use ZyndPay\Resources\Balances;
use ZyndPay\Resources\Payins;
use ZyndPay\Resources\Transactions;
use ZyndPay\Resources\Withdrawals;

/**
 * Official ZyndPay PHP SDK.
 *
 * Usage:
 *   $zyndpay = new \ZyndPay\ZyndPay('zyp_live_sk_...');
 *
 *   // Create a payin
 *   $payin = $zyndpay->payins->create(['amount' => '100']);
 *   echo $payin['depositAddress']; // Send USDT here
 *
 *   // Check balance
 *   $balance = $zyndpay->balances->get();
 *
 *   // Verify webhook
 *   $event = $zyndpay->webhooks->verify($payload, $signature);
 */
class ZyndPay
{
    public Payins $payins;
    public Withdrawals $withdrawals;
    public Transactions $transactions;
    public Balances $balances;
    public WebhookVerifier $webhooks;

    /**
     * @param string $apiKey Your API key (zyp_live_sk_... or zyp_test_sk_...)
     * @param array $options Optional: base_url, timeout, max_retries, webhook_secret
     */
    public function __construct(string $apiKey, array $options = [])
    {
        $client = new HttpClient(
            $apiKey,
            $options['base_url'] ?? null,
            $options['timeout'] ?? null,
            $options['max_retries'] ?? null,
        );

        $this->payins = new Payins($client);
        $this->withdrawals = new Withdrawals($client);
        $this->transactions = new Transactions($client);
        $this->balances = new Balances($client);
        $this->webhooks = new WebhookVerifier($options['webhook_secret'] ?? '');
    }
}
