<?php

declare(strict_types=1);

namespace ZyndPay\Resources;

use ZyndPay\HttpClient;

/**
 * WebhookEndpoints — CRUD for webhook endpoints registered against the
 * merchant account.
 *
 * Note: `$zyndpay->webhooks` is the *signature verifier* used to validate
 * incoming deliveries. This resource (`$zyndpay->webhookEndpoints`) is the
 * *management* surface — same split Stripe uses.
 *
 * Requires `webhooks_read` scope for read methods, `webhooks_write` for
 * mutations.
 */
class WebhookEndpoints
{
    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new webhook endpoint.
     *
     * The response includes a one-time `secret` field — store it; you will
     * need it to verify incoming deliveries via `$zyndpay->webhooks->verify(...)`.
     * The secret is never returned again.
     *
     * @param array       $params         {url, events, maxRetries?, retryBackoff?, retryIntervalSeconds?}
     * @param string|null $idempotencyKey Optional Idempotency-Key header value
     */
    public function create(array $params, ?string $idempotencyKey = null): array
    {
        $res = $this->client->post('/webhooks/endpoints', $params, $idempotencyKey);
        return $res['data'];
    }

    /** List all webhook endpoints. */
    public function list(): array
    {
        $res = $this->client->get('/webhooks/endpoints');
        return $res['data'];
    }

    /** Get a single endpoint by id. */
    public function get(string $id): array
    {
        $res = $this->client->get("/webhooks/endpoints/{$id}");
        return $res['data'];
    }

    /** Update url, event subscriptions, retry config, or enabled flag. */
    public function update(string $id, array $params): array
    {
        $res = $this->client->patch("/webhooks/endpoints/{$id}", $params);
        return $res['data'];
    }

    /** Delete a webhook endpoint. */
    public function delete(string $id): void
    {
        $this->client->delete("/webhooks/endpoints/{$id}");
    }

    /**
     * Rotate the signing secret for an endpoint. The response contains a
     * new one-time `secret` value — update your verifier configuration
     * before the next delivery arrives.
     */
    public function rotateSecret(string $id): array
    {
        $res = $this->client->patch("/webhooks/endpoints/{$id}/rotate-secret", []);
        return $res['data'];
    }

    /**
     * Reactivate an endpoint that was auto-suspended after 3 consecutive
     * delivery failures. Resets the failure counter to zero.
     */
    public function reactivate(string $id): array
    {
        $res = $this->client->patch("/webhooks/endpoints/{$id}/reactivate", []);
        return $res['data'];
    }

    /**
     * Send a synthetic test event. The body carries `data: { test: true, ... }`
     * so your handler can short-circuit it.
     *
     * @param array $params {endpointId, eventType}
     */
    public function sendTestEvent(array $params): array
    {
        $res = $this->client->post('/webhooks/test', $params);
        return $res['data'];
    }
}
