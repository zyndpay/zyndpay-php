<?php

namespace ZyndPay;

class WebhookVerifier
{
    public const HEADER_NAME = 'x-zyndpay-signature';

    // ── Payin events ────────────────────────────────────────────────
    // All payin events include base fields in data:
    //   transactionId, status, currency, chain, externalRef
    //
    // payin.created:    base + amount, address
    // payin.confirming: base + txHash, confirmations
    // payin.confirmed:  base + amount, amountRequested, txHash, confirmedAt
    // payin.expired:    base fields only
    public const PAYIN_CREATED    = 'payin.created';
    public const PAYIN_CONFIRMING = 'payin.confirming';
    public const PAYIN_CONFIRMED  = 'payin.confirmed';
    public const PAYIN_EXPIRED    = 'payin.expired';
    public const PAYIN_FAILED     = 'payin.failed';
    public const PAYIN_OVERPAID   = 'payin.overpaid';
    public const PAYIN_UNDERPAID  = 'payin.underpaid';

    // ── Deposit events ──────────────────────────────────────────────
    public const DEPOSIT_CONFIRMED = 'deposit.confirmed';
    public const DEPOSIT_FAILED    = 'deposit.failed';
    public const DEPOSIT_OVERPAID  = 'deposit.overpaid';
    public const DEPOSIT_UNDERPAID = 'deposit.underpaid';

    // ── Payout events ───────────────────────────────────────────────
    public const PAYOUT_BROADCAST = 'payout.broadcast';
    public const PAYOUT_CONFIRMED = 'payout.confirmed';
    public const PAYOUT_FAILED    = 'payout.failed';

    // ── Withdrawal events ───────────────────────────────────────────
    public const WITHDRAWAL_REQUESTED = 'withdrawal.requested';
    public const WITHDRAWAL_APPROVED  = 'withdrawal.approved';
    public const WITHDRAWAL_BROADCAST = 'withdrawal.broadcast';
    public const WITHDRAWAL_CONFIRMED = 'withdrawal.confirmed';
    public const WITHDRAWAL_FAILED    = 'withdrawal.failed';

    // ── Conversion events ───────────────────────────────────────────
    public const CONVERSION_CONFIRMED = 'conversion.confirmed';
    public const CONVERSION_FAILED    = 'conversion.failed';

    // ── Subscription events ─────────────────────────────────────────
    public const SUBSCRIPTION_CREATED            = 'subscription.created';
    public const SUBSCRIPTION_RENEWED            = 'subscription.renewed';
    public const SUBSCRIPTION_RENEWAL_INITIATED  = 'subscription.renewal_initiated';
    public const SUBSCRIPTION_FAILED             = 'subscription.failed';
    public const SUBSCRIPTION_CANCELLED          = 'subscription.cancelled';
    public const SUBSCRIPTION_PAUSED             = 'subscription.paused';
    public const SUBSCRIPTION_RESUMED            = 'subscription.resumed';
    public const SUBSCRIPTION_UPDATED            = 'subscription.updated';

    // ── Refund events ───────────────────────────────────────────────
    public const REFUND_CREATED   = 'refund.created';
    public const REFUND_APPROVED  = 'refund.approved';
    public const REFUND_REJECTED  = 'refund.rejected';
    public const REFUND_COMPLETED = 'refund.completed';
    public const REFUND_FAILED    = 'refund.failed';

    // ── Dispute events ──────────────────────────────────────────────
    public const DISPUTE_OPENED    = 'dispute.opened';
    public const DISPUTE_RESOLVED  = 'dispute.resolved';
    public const DISPUTE_REJECTED  = 'dispute.rejected';
    public const DISPUTE_ESCALATED = 'dispute.escalated';

    // ── AML events ──────────────────────────────────────────────────
    public const AML_FLAGGED = 'aml.flagged';

    // ── Marketplace / split-payment events ──────────────────────────
    public const SPLITPAYMENT_CREATED = 'splitpayment.created';

    /**
     * The full set of webhook event names the API delivers. Useful for
     * subscribing an endpoint to every event or for dropdown population.
     *
     * @var string[]
     */
    public const ALL_EVENTS = [
        self::PAYIN_CREATED,
        self::PAYIN_CONFIRMING,
        self::PAYIN_CONFIRMED,
        self::PAYIN_EXPIRED,
        self::PAYIN_FAILED,
        self::PAYIN_OVERPAID,
        self::PAYIN_UNDERPAID,
        self::DEPOSIT_CONFIRMED,
        self::DEPOSIT_FAILED,
        self::DEPOSIT_OVERPAID,
        self::DEPOSIT_UNDERPAID,
        self::PAYOUT_BROADCAST,
        self::PAYOUT_CONFIRMED,
        self::PAYOUT_FAILED,
        self::WITHDRAWAL_REQUESTED,
        self::WITHDRAWAL_APPROVED,
        self::WITHDRAWAL_BROADCAST,
        self::WITHDRAWAL_CONFIRMED,
        self::WITHDRAWAL_FAILED,
        self::CONVERSION_CONFIRMED,
        self::CONVERSION_FAILED,
        self::SUBSCRIPTION_CREATED,
        self::SUBSCRIPTION_RENEWED,
        self::SUBSCRIPTION_RENEWAL_INITIATED,
        self::SUBSCRIPTION_FAILED,
        self::SUBSCRIPTION_CANCELLED,
        self::SUBSCRIPTION_PAUSED,
        self::SUBSCRIPTION_RESUMED,
        self::SUBSCRIPTION_UPDATED,
        self::REFUND_CREATED,
        self::REFUND_APPROVED,
        self::REFUND_REJECTED,
        self::REFUND_COMPLETED,
        self::REFUND_FAILED,
        self::DISPUTE_OPENED,
        self::DISPUTE_RESOLVED,
        self::DISPUTE_REJECTED,
        self::DISPUTE_ESCALATED,
        self::AML_FLAGGED,
        self::SPLITPAYMENT_CREATED,
    ];

    private string $signingSecret;

    public function __construct(string $signingSecret)
    {
        $this->signingSecret = $signingSecret;
    }

    /**
     * Verify a webhook signature and parse the event payload.
     *
     * @param string $payload The raw request body
     * @param string $signature The value of the X-ZyndPay-Signature header
     * @param int $toleranceSeconds Max age of the event in seconds (default: 300)
     * @return array The parsed webhook event
     * @throws \InvalidArgumentException If the signature is invalid or the event is too old
     *
     * Example:
     *   $event = $zyndpay->webhooks->verify($payload, $_SERVER['HTTP_X_ZYNDPAY_SIGNATURE']);
     *   echo $event['event']; // "payin.confirmed"
     */
    public function verify(string $payload, string $signature, int $toleranceSeconds = 300): array
    {
        if (empty($signature)) {
            throw new \InvalidArgumentException('Missing webhook signature header');
        }

        $parts = explode(',', $signature);
        $tPart = null;
        $v1Part = null;

        foreach ($parts as $part) {
            if (str_starts_with($part, 't=')) {
                $tPart = $part;
            } elseif (str_starts_with($part, 'v1=')) {
                $v1Part = $part;
            }
        }

        if ($tPart === null || $v1Part === null) {
            throw new \InvalidArgumentException('Invalid webhook signature format — expected "t=<timestamp>,v1=<hmac>"');
        }

        $timestamp = (int)str_replace('t=', '', $tPart);
        $receivedHmac = str_replace('v1=', '', $v1Part);

        $now = time();
        if (abs($now - $timestamp) > $toleranceSeconds) {
            throw new \InvalidArgumentException(
                sprintf('Webhook timestamp too old (%ds > %ds tolerance)', abs($now - $timestamp), $toleranceSeconds)
            );
        }

        $signedPayload = "{$timestamp}.{$payload}";
        $expectedHmac = hash_hmac('sha256', $signedPayload, $this->signingSecret);

        if (!hash_equals($expectedHmac, $receivedHmac)) {
            throw new \InvalidArgumentException('Webhook signature verification failed');
        }

        return json_decode($payload, true);
    }
}
