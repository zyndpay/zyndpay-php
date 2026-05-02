<?php

declare(strict_types=1);

namespace ZyndPay\Resources;

use ZyndPay\HttpClient;

/**
 * Wallets resource — lists the merchant's wallets and manages the
 * address whitelist (the set of TRON addresses authorized to receive
 * withdrawals or payouts).
 *
 * Whitelist methods require an API key with the `wallets_write` scope
 * (mutations) or `wallets_read` (listing). A 24-hour security cooldown
 * applies to every newly added address.
 */
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

    // ── Address whitelist ──────────────────────────────────────────────

    /**
     * List whitelisted TRON addresses.
     *
     * @param array $params Optional: ['context' => 'WITHDRAWAL'|'PAYOUT']
     */
    public function listWhitelist(array $params = []): array
    {
        $res = $this->client->get('/wallets/whitelist', $params);
        return $res['data'];
    }

    /**
     * Add a single TRON address to the whitelist.
     *
     * @param array $params {currency, chain, address, label?, usageContexts?, totpCode?}
     */
    public function addToWhitelist(array $params): array
    {
        $res = $this->client->post('/wallets/whitelist', $params);
        return $res['data'];
    }

    /**
     * Add up to 500 addresses in one call. The optional `usageContexts`
     * array is applied to every address in the batch (defaults to ['WITHDRAWAL']).
     *
     * @param array $params {addresses: [{address, label?}], usageContexts?, totpCode?}
     */
    public function bulkAddToWhitelist(array $params): array
    {
        $res = $this->client->post('/wallets/whitelist/bulk', $params);
        return $res['data'];
    }

    /**
     * Replace the usageContexts on an existing whitelist entry without
     * restarting the 24-hour cooldown.
     *
     * @param string $id            Whitelist entry id
     * @param array  $usageContexts e.g. ['WITHDRAWAL', 'PAYOUT']
     */
    public function updateWhitelistContexts(string $id, array $usageContexts): array
    {
        $res = $this->client->patch("/wallets/whitelist/{$id}/contexts", [
            'usageContexts' => $usageContexts,
        ]);
        return $res['data'];
    }

    /** Remove an address from the whitelist. */
    public function removeFromWhitelist(string $id): void
    {
        $this->client->delete("/wallets/whitelist/{$id}");
    }
}
