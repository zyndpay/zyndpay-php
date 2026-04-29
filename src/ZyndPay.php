<?php

declare(strict_types=1);

namespace ZyndPay;

use ZyndPay\Resources\Balances;
use ZyndPay\Resources\BulkPayments;
use ZyndPay\Resources\Conversions;
use ZyndPay\Resources\FiatDestinations;
use ZyndPay\Resources\Payins;
use ZyndPay\Resources\Paylinks;
use ZyndPay\Resources\Payouts;
use ZyndPay\Resources\Transactions;
use ZyndPay\Resources\Wallets;
use ZyndPay\Resources\Withdrawals;

/**
 * Official ZyndPay PHP SDK.
 *
 * Usage:
 *   $zyndpay = new \ZyndPay\ZyndPay('zyp_live_sk_...');
 *
 *   // Create a payin
 *   $payin = $zyndpay->payins->create(['amount' => '100']);
 *   echo $payin['address']; // Send USDT here
 *
 *   // Create a payout
 *   $payout = $zyndpay->payouts->create([
 *       'amount' => '50',
 *       'destinationAddress' => 'T...',
 *   ]);
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
    public Payouts $payouts;
    public Paylinks $paylinks;
    public BulkPayments $bulkPayments;
    public Withdrawals $withdrawals;
    public Transactions $transactions;
    public Balances $balances;
    public Wallets $wallets;
    public FiatDestinations $fiatDestinations;
    public Conversions $conversions;
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
        $this->payouts = new Payouts($client);
        $this->paylinks = new Paylinks($client);
        $this->bulkPayments = new BulkPayments($client);
        $this->withdrawals = new Withdrawals($client);
        $this->transactions = new Transactions($client);
        $this->balances = new Balances($client);
        $this->wallets = new Wallets($client);
        $this->fiatDestinations = new FiatDestinations($client);
        $this->conversions = new Conversions($client);
        $this->webhooks = new WebhookVerifier($options['webhook_secret'] ?? '');
    }
}
