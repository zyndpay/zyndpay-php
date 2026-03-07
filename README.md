# zyndpay-php

Official ZyndPay PHP SDK — accept USDT TRC20 payments with a few lines of code.

[![Packagist Version](https://img.shields.io/packagist/v/zyndpay/zyndpay-php)](https://packagist.org/packages/zyndpay/zyndpay-php)
[![PHP](https://img.shields.io/packagist/php-v/zyndpay/zyndpay-php)](https://packagist.org/packages/zyndpay/zyndpay-php)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

---

## Requirements

- PHP 8.0+
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
    'amount'          => '100',                      // USDT amount (minimum 20)
    'externalRef'     => 'order_9f8e7d',             // your internal order ID (optional)
    'expiresInSeconds'=> 3600,                       // 1 hour — default is 24h (optional)
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
echo $withdrawal['fee'];        // "2.00" (flat $2 fee)
echo $withdrawal['netAmount'];  // "48.00"
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

switch ($event['type']) {
    case 'payin.confirmed':
        // credit the order in your system
        error_log('Payment confirmed: ' . $event['data']['id']);
        break;

    case 'withdrawal.confirmed':
        error_log('Withdrawal confirmed: ' . $event['data']['id']);
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
use ZyndPay\Exceptions\RateLimitException;

try {
    $payin = $zyndpay->payins->create(['amount' => '5']); // below minimum
} catch (ValidationException $e) {
    echo 'Bad request: ' . $e->getMessage();   // "amount must be >= 20"
    echo 'Status code: ' . $e->statusCode;     // 400
} catch (AuthenticationException $e) {
    echo 'Invalid API key';
} catch (NotFoundException $e) {
    echo 'Resource not found';
} catch (RateLimitException $e) {
    echo 'Rate limited, retry after ' . $e->retryAfter . 's';
} catch (ZyndPayException $e) {
    echo 'API error ' . $e->statusCode . ': ' . $e->getMessage();
}
```

---

## License

MIT — see [LICENSE](LICENSE)
