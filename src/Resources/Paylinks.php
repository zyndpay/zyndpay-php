<?php

declare(strict_types=1);

namespace ZyndPay\Resources;

use ZyndPay\HttpClient;

class Paylinks
{
    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new payment link.
     *
     * @param array $params {
     *   @type string $type FIXED, VARIABLE, or RECURRING (default: FIXED)
     *   @type array $products Array of product definitions
     *   @type int $expiresInSeconds Expiry in seconds (0 = no expiry)
     *   @type int $maxUses Maximum number of uses
     *   @type string $successUrl Post-payment redirect URL
     *   @type string $cancelUrl Cancel redirect URL
     *   @type string $collectName REQUIRED, OPTIONAL, or HIDDEN
     *   @type string $collectEmail REQUIRED, OPTIONAL, or HIDDEN
     *   @type string $collectPhone REQUIRED, OPTIONAL, or HIDDEN
     *   @type string $collectAddress REQUIRED, OPTIONAL, or HIDDEN
     *   @type string $recurringInterval WEEKLY, MONTHLY, or YEARLY
     *   @type int $recurringIntervalCount Interval count
     *   @type string $brandColor Brand color hex
     *   @type string $logoUrl Logo URL
     * }
     * @return array Paylink object
     */
    public function create(array $params, bool $sandbox = false): array
    {
        $query = $sandbox ? ['sandbox' => 'true'] : [];
        $res = $this->client->post('/paylinks', $params, null, $query);
        return $res['data'];
    }

    /**
     * Get a paylink by ID.
     */
    public function get(string $id): array
    {
        $res = $this->client->get("/paylinks/{$id}");
        return $res['data'];
    }

    /**
     * List paylinks with optional filters.
     *
     * @param array $params Optional: page, limit, status
     * @return array{items: array, total: int}
     */
    public function list(array $params = []): array
    {
        $res = $this->client->get('/paylinks', $params);
        return $res['data'];
    }

    /**
     * @deprecated Use list() instead
     */
    public function listAll(array $params = []): array
    {
        return $this->list($params);
    }

    /**
     * Update a paylink.
     *
     * @param string $id Paylink ID
     * @param array $params Fields to update: status, maxUses, successUrl, cancelUrl, etc.
     * @return array Updated paylink
     */
    public function update(string $id, array $params): array
    {
        $res = $this->client->patch("/paylinks/{$id}", $params);
        return $res['data'];
    }

    /**
     * Delete a paylink.
     */
    public function delete(string $id): void
    {
        $this->client->delete("/paylinks/{$id}");
    }

    /**
     * Get stats for a specific paylink.
     */
    public function getStats(string $id): array
    {
        $res = $this->client->get("/paylinks/{$id}/stats");
        return $res['data'];
    }

    /**
     * Get overall paylink dashboard stats.
     */
    public function getDashboardStats(): array
    {
        $res = $this->client->get('/paylinks/dashboard-stats');
        return $res['data'];
    }

    /**
     * List orders for a paylink.
     *
     * @param string $id Paylink ID
     * @param array $params Optional: page, limit
     * @return array{items: array, total: int}
     */
    public function listOrders(string $id, array $params = []): array
    {
        $res = $this->client->get("/paylinks/{$id}/orders", $params);
        return $res['data'];
    }

    /**
     * Export orders as CSV.
     */
    public function exportOrdersCsv(string $id): string
    {
        return $this->client->getRaw("/paylinks/{$id}/orders/export");
    }

    /**
     * Create a promo code for a paylink.
     *
     * @param string $id Paylink ID
     * @param array $params {
     *   @type string $code Promo code string
     *   @type string $discountType PERCENT or FIXED
     *   @type string $discountValue Discount value
     *   @type int $maxUses Maximum redemptions
     * }
     * @return array Promo code object
     */
    public function createPromoCode(string $id, array $params): array
    {
        $res = $this->client->post("/paylinks/{$id}/promo-codes", $params);
        return $res['data'];
    }

    /**
     * List promo codes for a paylink.
     */
    public function listPromoCodes(string $id): array
    {
        $res = $this->client->get("/paylinks/{$id}/promo-codes");
        return $res['data'];
    }

    /**
     * Toggle a promo code active/inactive.
     *
     * @param string $paylinkId Paylink ID
     * @param string $promoId Promo code ID
     * @param bool $isActive Whether the promo code should be active
     * @return array Updated promo code
     */
    public function togglePromoCode(string $paylinkId, string $promoId, bool $isActive): array
    {
        $res = $this->client->patch("/paylinks/{$paylinkId}/promo-codes/{$promoId}", ['isActive' => $isActive]);
        return $res['data'];
    }

    /**
     * Delete a promo code.
     */
    public function deletePromoCode(string $paylinkId, string $promoId): void
    {
        $this->client->delete("/paylinks/{$paylinkId}/promo-codes/{$promoId}");
    }

    /**
     * Create a paylink template.
     *
     * @param array $params { name: string, config: array }
     * @return array Template object
     */
    public function createTemplate(array $params): array
    {
        $res = $this->client->post('/paylinks/templates', $params);
        return $res['data'];
    }

    /**
     * List paylink templates.
     */
    public function listTemplates(): array
    {
        $res = $this->client->get('/paylinks/templates');
        return $res['data'];
    }

    /**
     * Delete a template.
     */
    public function deleteTemplate(string $id): void
    {
        $this->client->delete("/paylinks/templates/{$id}");
    }

    /**
     * Save an existing paylink as a template.
     *
     * @param string $id Paylink ID
     * @param string $name Template name
     * @return array Template object
     */
    public function saveAsTemplate(string $id, string $name): array
    {
        $res = $this->client->post("/paylinks/{$id}/save-as-template", ['name' => $name]);
        return $res['data'];
    }

    /**
     * List subscriptions for a recurring paylink.
     *
     * @param string $id Paylink ID
     * @param array $params Optional: page, limit
     * @return array{items: array, total: int}
     */
    public function listSubscriptions(string $id, array $params = []): array
    {
        $res = $this->client->get("/paylinks/{$id}/subscriptions", $params);
        return $res['data'];
    }

    /**
     * Cancel a subscription.
     */
    public function cancelSubscription(string $paylinkId, string $subscriptionId): array
    {
        $res = $this->client->post("/paylinks/{$paylinkId}/subscriptions/{$subscriptionId}/cancel");
        return $res['data'];
    }

    // ─── Images ──────────────────────────────────────────────────────────────

    /**
     * Upload a cover image for a paylink.
     *
     * @param string $paylinkId Paylink ID
     * @param string $filePath Path to image file
     * @return array{coverImageUrl: string}
     */
    public function uploadCoverImage(string $paylinkId, string $filePath): array
    {
        $res = $this->client->postFile("/paylinks/{$paylinkId}/cover-image", $filePath, 'image');
        return $res['data'];
    }

    /**
     * Delete cover image from a paylink.
     */
    public function deleteCoverImage(string $paylinkId): void
    {
        $this->client->delete("/paylinks/{$paylinkId}/cover-image");
    }

    /**
     * Upload a product image.
     *
     * @param string $paylinkId Paylink ID
     * @param string $productId Product ID
     * @param string $filePath Path to image file
     * @return array{imageUrl: string}
     */
    public function uploadProductImage(string $paylinkId, string $productId, string $filePath): array
    {
        $res = $this->client->postFile("/paylinks/{$paylinkId}/products/{$productId}/image", $filePath, 'image');
        return $res['data'];
    }

    /**
     * Bulk import products from a CSV file.
     *
     * @param string $paylinkId Paylink ID
     * @param string $filePath Path to CSV file
     * @return array{imported: int}
     */
    public function importProductsCsv(string $paylinkId, string $filePath): array
    {
        $res = $this->client->postFile("/paylinks/{$paylinkId}/products/import-csv", $filePath);
        return $res['data'];
    }
}
