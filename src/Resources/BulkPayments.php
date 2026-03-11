<?php

namespace ZyndPay\Resources;

use ZyndPay\HttpClient;

class BulkPayments
{
    private HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new bulk payment batch.
     *
     * @param array $params Optional: label
     * @param string|null $idempotencyKey Optional idempotency key
     * @return array Batch object
     */
    public function createBatch(array $params = [], ?string $idempotencyKey = null): array
    {
        $res = $this->client->post('/bulk-payments', $params, $idempotencyKey);
        return $res['data'];
    }

    /**
     * Get a batch by ID (includes items).
     */
    public function getBatch(string $id): array
    {
        $res = $this->client->get("/bulk-payments/{$id}");
        return $res['data'];
    }

    /**
     * List batches with optional filters.
     *
     * @param array $params Optional: page, limit, status
     * @return array{data: array, meta: array}
     */
    public function listBatches(array $params = []): array
    {
        $res = $this->client->get('/bulk-payments', $params);
        return ['data' => $res['data'], 'meta' => $res['meta'] ?? null];
    }

    /**
     * Add payment items to a draft batch.
     *
     * @param string $batchId Batch ID
     * @param array $items Array of items, each with: walletAddress, amount, recipientName?, reference?, description?
     * @return array Updated batch
     */
    public function addItems(string $batchId, array $items): array
    {
        $res = $this->client->post("/bulk-payments/{$batchId}/items", ['items' => $items]);
        return $res['data'];
    }

    /**
     * Upload a CSV or XLSX file of payment items.
     *
     * @param string $batchId Batch ID
     * @param string $filePath Path to CSV or XLSX file
     * @return array Import result
     */
    public function importFile(string $batchId, string $filePath): array
    {
        $res = $this->client->postFile("/bulk-payments/{$batchId}/import", $filePath);
        return $res['data'];
    }

    /**
     * Validate a batch before execution (calculates fees, checks balance).
     */
    public function validate(string $batchId): array
    {
        $res = $this->client->post("/bulk-payments/{$batchId}/validate");
        return $res['data'];
    }

    /**
     * Execute a validated batch (reserves funds and starts processing).
     */
    public function execute(string $batchId): array
    {
        $res = $this->client->post("/bulk-payments/{$batchId}/execute");
        return $res['data'];
    }

    /**
     * Retry failed items in a batch.
     */
    public function retry(string $batchId): array
    {
        $res = $this->client->post("/bulk-payments/{$batchId}/retry");
        return $res['data'];
    }

    /**
     * Cancel a draft or validated batch.
     */
    public function cancel(string $batchId): array
    {
        $res = $this->client->post("/bulk-payments/{$batchId}/cancel");
        return $res['data'];
    }

    /**
     * Export batch items as CSV.
     *
     * @param string $batchId Batch ID
     * @return mixed CSV content
     */
    public function export(string $batchId)
    {
        $res = $this->client->get("/bulk-payments/{$batchId}/export");
        return $res['data'];
    }

    /**
     * Download template file.
     *
     * @param string $format 'csv' or 'xlsx'
     * @return mixed Template content
     */
    public function downloadTemplate(string $format = 'csv')
    {
        $res = $this->client->get('/bulk-payments/template', ['format' => $format]);
        return $res['data'];
    }
}
