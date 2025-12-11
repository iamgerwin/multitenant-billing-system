<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface InvoiceRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get invoices by status.
     */
    public function getByStatus(InvoiceStatus $status): Collection;

    /**
     * Get all pending invoices.
     */
    public function getPending(): Collection;

    /**
     * Get all overdue invoices.
     */
    public function getOverdue(): Collection;

    /**
     * Find invoice by invoice number.
     */
    public function findByInvoiceNumber(string $invoiceNumber): ?Invoice;

    /**
     * Get invoices for a specific vendor.
     */
    public function getByVendor(int $vendorId): Collection;

    /**
     * List invoices with filters, sorting, and pagination.
     */
    public function listWithFilters(
        ?InvoiceStatus $status = null,
        ?int $vendorId = null,
        string $sortField = 'created_at',
        string $sortDirection = 'desc',
        int $perPage = 15
    ): LengthAwarePaginator;

    /**
     * Find invoice with specified relations loaded.
     */
    public function findWithRelations(int $id, array $relations = []): ?Invoice;
}
