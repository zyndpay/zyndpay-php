# zyndpay-php

Official ZyndPay PHP SDK — accept USDT TRC20 payments with a few lines of code.

[![Packagist Version](https://img.shields.io/packagist/v/zyndpay/zyndpay-php)](https://packagist.org/packages/zyndpay/zyndpay-php)
[![PHP](https://img.shields.io/packagist/php-v/zyndpay/zyndpay-php)](https://packagist.org/packages/zyndpay/zyndpay-php)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

---

## Requirements

- PHP 8.1+
- `ext-curl` and `ext-json` extensions (enabled by default in most PHP installs)
- A ZyndPay account and API key

---

## Installation

```bash
composer require zyndpay/zyndpay-php
```

---

## Quickstart

```php
<?php

require 'vendor/autoload.php';

$zyndpay = new \ZyndPay\ZyndPay('zyp_live_sk_...');

// Create a payment request
$payin = $zyndpay->payins->create(['amount' => '100']);
echo $payin['depositAddress'];  // Send USDT TRC20 here
echo $payin['paymentPageUrl'];  // Redirect your customer here

// Check your balance
$balance = $zyndpay->balances->get();
echo $balance['available'];  // e.g. "97.00"
```

---

## Configuration

```php
$zyndpay = new \ZyndPay\ZyndPay('zyp_live_sk_...', [
    'webhook_secret' => 'whsec_...',              // optional — needed for webhook verification
    'base_url'       => 'https://api.zyndpay.io/v1', // optional — override for self-hosted
    'timeout'        => 30,                        // optional — seconds (default: 30)
    'max_retries'    => 2,                         // optional — retries on network errors (default: 2)
]);
```

### API key types

| Prefix | Type |
|---|---|
| `zyp_live_sk_` | Live secret key |
| `zyp_live_pk_` | Live publishable key |
| `zyp_test_sk_` | Sandbox secret key |
| `zyp_test_pk_` | Sandbox publishable key |

---

## Payins

### Create a payin

```php
$payin = $zyndpay->payins->create([
    'amount'          => '100',                      // USDT amount (minimum 1)
    'externalRef'     => 'order_9f8e7d',             // your internal order ID (optional)
    'expiresInSeconds'=> 3600,                       // 1 hour — default is 30min (optional)
    'metadata'        => ['userId' => 'usr_123'],    // stored as-is (optional)
    'successUrl'      => 'https://yoursite.com/success',
    'cancelUrl'       => 'https://yoursite.com/cancel',
]);

echo $payin['id'];              // "pay_..."
echo $payin['depositAddress']; // TRC20 address to receive payment
echo $payin['amount'];         // "100"
echo $payin['fee'];            // "3.00"
echo $payin['netAmount'];      // "97.00"
echo $payin['status'];         // "AWAITING_PAYMENT"
echo $payin['expiresAt'];      // ISO timestamp
```

### Get a payin

```php
$payin = $zyndpay->payins->get('pay_abc123');
```

### List payins

```php
$result = $zyndpay->payins->list(['status' => 'CONFIRMED', 'page' => 1, 'limit' => 20]);

foreach ($result['data'] as $payin) {
    echo $payin['id'] . ': ' . $payin['status'] . PHP_EOL;
}

$meta = $result['meta'];
echo $meta['total'] . ' total, ' . $meta['totalPages'] . ' pages';
```

### Payin statuses

| Status | Description |
|---|---|
| `PENDING` | Just created |
| `AWAITING_PAYMENT` | Deposit address assigned, waiting for funds |
| `CONFIRMING` | Payment detected, waiting for confirmations |
| `CONFIRMED` | Payment confirmed — balance credited |
| `EXPIRED` | Payment window elapsed |
| `OVERPAID` | More than expected was sent |
| `UNDERPAID` | Less than expected was sent |
| `FAILED` | Processing failed |

---

## Wallets, Conversions, and FCFA Payouts (multi-wallet API)

The multi-wallet API exposes one balance per `(currency, rail)` pair — for example a `USDT_TRC20` wallet plus an `XOF` mobile-money wallet.

```php
// 1. List wallets
$wallets = $zyndpay->wallets->list();
$usdt = array_filter($wallets, fn($w) => $w['currency'] === 'USDT_TRC20')[0];
$xof  = array_filter($wallets, fn($w) => $w['currency'] === 'XOF')[0];

// 2. Whitelist an FCFA mobile-money destination
$dest = $zyndpay->fiatDestinations->create([
    'kind'         => 'MOMO',
    'label'        => 'My Orange',
    'momoOperator' => 'ORANGE',
    'momoPhone'    => '22670000000',
    'isPrimary'    => true,
]);

// 3. Convert USDT → XOF (synchronous wallet-to-wallet)
$zyndpay->conversions->convertBetweenWallets([
    'fromWalletId' => $usdt['id'],
    'toWalletId'   => $xof['id'],
    'fromAmount'   => '100',
]);

// 4. Pay the FCFA balance out to the whitelisted destination
$zyndpay->withdrawals->create([
    'amount'            => '60000',
    'walletId'          => $xof['id'],
    'fiatDestinationId' => $dest['id'],
]);
```

> The legacy `conversions->create(...)` is deprecated (sunset 2026-07-25). Use the two-step `convertBetweenWallets` + `withdrawals->create` flow above.

---

## Paylinks

Payment links you can share with customers — fixed-price, variable-price, or recurring.

### Create a paylink

```php
$paylink = $zyndpay->paylinks->create([
    'title'       => 'Premium Plan',
    'type'        => 'FIXED',    // 'FIXED' | 'VARIABLE' | 'RECURRING'
    'amount'      => '25',       // USDT — omit for VARIABLE
    'currency'    => 'USD',
    'description' => 'Monthly subscription',
    'successUrl'  => 'https://yoursite.com/thank-you',
    'cancelUrl'   => 'https://yoursite.com/cancel',
]);

echo $paylink['id'];      // "plk_abc123"
echo $paylink['url'];     // shareable payment URL
echo $paylink['status'];  // "ACTIVE"
```

### Get / list / update / delete

```php
$paylink = $zyndpay->paylinks->get('plk_abc123');

$result = $zyndpay->paylinks->list(['status' => 'ACTIVE', 'page' => 1, 'limit' => 20]);
foreach ($result['items'] as $pl) {
    echo $pl['id'] . ': ' . $pl['status'] . PHP_EOL;
}

$zyndpay->paylinks->update('plk_abc123', ['title' => 'New Title']);
$zyndpay->paylinks->delete('plk_abc123');
```

### Stats and orders

```php
$stats = $zyndpay->paylinks->getStats('plk_abc123');
echo $stats['totalRevenue'] . ' — ' . $stats['orderCount'] . ' orders';

$dashStats = $zyndpay->paylinks->getDashboardStats();

$orders = $zyndpay->paylinks->listOrders('plk_abc123', ['page' => 1, 'limit' => 50]);
$csv = $zyndpay->paylinks->exportOrdersCsv('plk_abc123');
```

### Promo codes

```php
$promo = $zyndpay->paylinks->createPromoCode('plk_abc123', [
    'code'          => 'SAVE10',
    'discountType'  => 'PERCENT',
    'discountValue' => '10',
    'maxUses'       => 100,
]);
$codes = $zyndpay->paylinks->listPromoCodes('plk_abc123');
$zyndpay->paylinks->togglePromoCode('plk_abc123', $promo['id'], false);
$zyndpay->paylinks->deletePromoCode('plk_abc123', $promo['id']);
```

### Templates

```php
$tpl = $zyndpay->paylinks->createTemplate(['title' => 'My Template', 'type' => 'FIXED', 'amount' => '50']);
$zyndpay->paylinks->saveAsTemplate('plk_abc123', 'Saved template');
$templates = $zyndpay->paylinks->listTemplates();
$zyndpay->paylinks->deleteTemplate($tpl['id']);
```

### Subscriptions (recurring paylinks)

```php
$subs = $zyndpay->paylinks->listSubscriptions('plk_abc123');
$zyndpay->paylinks->cancelSubscription('plk_abc123', $subs[0]['id']);
```

---

## Payouts

Send USDT directly to an external wallet address.

### Estimate fees before submitting

```php
$estimate = $zyndpay->payouts->estimate([
    'amount'             => '200',
    'destinationAddress' => 'TXxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    'currency'           => 'USDT_TRC20',
    'chain'              => 'TRON',
]);
echo $estimate['fee'];        // network fee in USDT
echo $estimate['netAmount'];  // amount recipient receives
```

### Create a payout

```php
$payout = $zyndpay->payouts->create(
    [
        'amount'             => '200',
        'destinationAddress' => 'TXxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'currency'           => 'USDT_TRC20',  // default
        'chain'              => 'TRON',         // default
        'externalRef'        => 'payout_order_789',
        'metadata'           => ['note' => 'vendor payment'],
    ],
    'idempotency-key-456' // optional
);
echo $payout['status']; // "PENDING" → "BROADCAST" → "CONFIRMED"
```

### Get / list payouts

```php
$tx = $zyndpay->payouts->get('payout_id');

$result = $zyndpay->payouts->list(['status' => 'CONFIRMED', 'page' => 1, 'limit' => 50]);
```

---

## Bulk Payments

Send to hundreds of addresses in a single batch — draft → validate → execute lifecycle.

```php
// 1. Create a draft batch
$batch = $zyndpay->bulkPayments->create([], 'idempotency-key');

// 2. Add recipients
$zyndpay->bulkPayments->addItems($batch['id'], [
    ['destinationAddress' => 'TXaaa...', 'amount' => '50', 'externalRef' => 'emp_1'],
    ['destinationAddress' => 'TXbbb...', 'amount' => '75', 'externalRef' => 'emp_2'],
]);

// Or import from a CSV/XLSX file
// $zyndpay->bulkPayments->importFile($batch['id'], '/path/to/payroll.csv');

// 3. Validate (checks balance, calculates fees)
$validated = $zyndpay->bulkPayments->validate($batch['id']);
echo $validated['totalAmount'] . ' / fee: ' . $validated['totalFee'];

// 4. Execute
$executed = $zyndpay->bulkPayments->execute($batch['id']);
echo $executed['status']; // "PROCESSING"

// 5. Monitor
$detail = $zyndpay->bulkPayments->get($batch['id']);
// $detail['items'] — per-recipient status

// Retry failed items / cancel
$zyndpay->bulkPayments->retry($batch['id']);
$zyndpay->bulkPayments->cancel($batch['id']);

// Export results as CSV
$csv = $zyndpay->bulkPayments->export($batch['id']);
```

### Batch statuses

| Status | Description |
|---|---|
| `DRAFT` | Building the batch |
| `VALIDATED` | Fees calculated, ready to execute |
| `PROCESSING` | Items being broadcast |
| `COMPLETED` | All items settled |
| `PARTIALLY_COMPLETED` | Some items failed |
| `CANCELLED` | Cancelled before execution |

---

## Sandbox / Test Mode

Use your sandbox API key (`zyp_test_sk_...`) and pass `sandbox: true` in the params. Then call `simulate` to instantly confirm it without real funds.

```php
$zyndpay = new \ZyndPay\ZyndPay('zyp_test_sk_...');

// Create a sandbox payin
$payin = $zyndpay->payins->create([
    'amount'  => '100',
    'sandbox' => true,
]);

// Instantly simulate confirmation
$confirmed = $zyndpay->payins->simulate($payin['id']);
echo $confirmed['status']; // "CONFIRMED"
```

---

## Withdrawals

### Request a withdrawal

```php
$withdrawal = $zyndpay->withdrawals->create(
    ['amount' => '50'],       // USDT amount
    'idempotency-key-123'     // optional idempotency key
);

echo $withdrawal['status'];     // "PENDING_REVIEW"
echo $withdrawal['fee'];        // "1.50" (1% fee, min $1.50)
echo $withdrawal['netAmount'];  // "48.50"
```

### Get / list withdrawals

```php
$withdrawal = $zyndpay->withdrawals->get('wdr_abc123');

$result = $zyndpay->withdrawals->list(['status' => 'CONFIRMED', 'page' => 1, 'limit' => 20]);
```

### Cancel a withdrawal

```php
$zyndpay->withdrawals->cancel('wdr_abc123'); // only while PENDING_REVIEW
```

### Withdrawal statuses

| Status | Description |
|---|---|
| `PENDING_REVIEW` | Awaiting admin approval |
| `APPROVED` | Approved, queued for processing |
| `PROCESSING` | Being broadcast to blockchain |
| `BROADCAST` | Transaction sent |
| `CONFIRMED` | On-chain confirmed |
| `REJECTED` | Rejected by admin |
| `CANCELLED` | Cancelled by merchant |
| `FAILED` | Broadcast failed |

---

## Transactions

```php
// Get a single transaction
$tx = $zyndpay->transactions->get('txn_abc123');

// List with filters
$result = $zyndpay->transactions->list([
    'type'      => 'PAYIN',       // 'PAYIN' | 'PAYOUT'
    'status'    => 'CONFIRMED',
    'from_date' => '2026-01-01',
    'to_date'   => '2026-03-31',
    'page'      => 1,
    'limit'     => 50,
]);
```

---

## Balances

```php
$balance = $zyndpay->balances->get();
echo $balance['currency'];   // "USDT_TRC20"
echo $balance['available'];  // spendable balance
echo $balance['pending'];    // in-flight / unconfirmed
echo $balance['total'];      // available + pending
```

---

## Webhooks

ZyndPay sends signed webhook events to your endpoint. Always verify the signature before processing.

### Verify a webhook

```php
<?php

require 'vendor/autoload.php';

$zyndpay = new \ZyndPay\ZyndPay('zyp_live_sk_...', [
    'webhook_secret' => 'whsec_...',
]);

// IMPORTANT: read the raw body — do not decode JSON before verifying
$payload   = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_ZYNDPAY_SIGNATURE'] ?? '';

try {
    $event = $zyndpay->webhooks->verify($payload, $signature);
} catch (\InvalidArgumentException $e) {
    http_response_code(400);
    echo 'Webhook signature verification failed';
    exit;
}

// All payin events include: transactionId, status, currency, chain, externalRef
switch ($event['event']) {
    case 'payin.confirmed':
        // Also has: amount, amountRequested, txHash, confirmedAt
        error_log('Payment confirmed: ' . $event['data']['externalRef'] . ' — ' . $event['data']['amount']);
        break;

    case 'payin.expired':
        error_log('Payment expired: ' . $event['data']['externalRef']);
        break;

    case 'withdrawal.confirmed':
        error_log('Withdrawal confirmed: ' . $event['data']['transactionId']);
        break;
}

http_response_code(200);
echo json_encode(['received' => true]);
```

### Webhook event types

| Event | Trigger |
|---|---|
| `payin.created` | Payin created |
| `payin.confirming` | Payment detected on-chain |
| `payin.confirmed` | Payment fully confirmed |
| `payin.expired` | Payin expired before payment |
| `payin.overpaid` | More than expected received |
| `payin.underpaid` | Less than expected received |
| `payin.failed` | Processing error |
| `withdrawal.requested` | Withdrawal created |
| `withdrawal.approved` | Approved by admin |
| `withdrawal.rejected` | Rejected by admin |
| `withdrawal.broadcast` | Sent to blockchain |
| `withdrawal.confirmed` | On-chain confirmed |
| `withdrawal.failed` | Broadcast failed |

---

## Error Handling

All exceptions extend `ZyndPayException` and include `statusCode` and an optional `requestId`.

```php
use ZyndPay\Exceptions\ZyndPayException;
use ZyndPay\Exceptions\AuthenticationException;
use ZyndPay\Exceptions\ValidationException;
use ZyndPay\Exceptions\NotFoundException;
use ZyndPay\Exceptions\ConflictException;
use ZyndPay\Exceptions\RateLimitException;

try {
    $payin = $zyndpay->payins->create(['amount' => '5']); // below minimum
} catch (ValidationException $e) {
    echo 'Bad request: ' . $e->getMessage();   // "amount must be >= 25"
    echo 'Status code: ' . $e->statusCode;     // 400
} catch (AuthenticationException $e) {
    echo 'Invalid API key';
} catch (NotFoundException $e) {
    echo 'Resource not found';
} catch (ConflictException $e) {
    echo 'Conflict: ' . $e->getMessage();
} catch (RateLimitException $e) {
    echo 'Rate limited, retry after ' . $e->retryAfter . 's';
} catch (ZyndPayException $e) {
    echo 'API error ' . $e->statusCode . ': ' . $e->getMessage();
}
```

---

## License

MIT — see [LICENSE](LICENSE)
