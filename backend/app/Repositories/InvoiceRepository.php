<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\InvoiceRepositoryInterface;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{
    /**
     * Whitelist of allowed sort fields.
     *
     * @var array<string>
     */
    private const ALLOWED_SORT_FIELDS = [
        'created_at',
        'updated_at',
        'invoice_date',
        'due_date',
        'total_amount',
        'invoice_number',
    ];

    /**
     * Create a new repository instance.
     */
    public function __construct(Invoice $model)
    {
        parent::__construct($model);
    }

    /**
     * Get invoices by status.
     */
    public function getByStatus(InvoiceStatus $status): Collection
    {
        return $this->model->withStatus($status)->get();
    }

    /**
     * Get all pending invoices.
     */
    public function getPending(): Collection
    {
        return $this->model->pending()->get();
    }

    /**
     * Get all overdue invoices.
     */
    public function getOverdue(): Collection
    {
        return $this->model->overdue()->get();
    }

    /**
     * Find invoice by invoice number.
     */
    public function findByInvoiceNumber(string $invoiceNumber): ?Invoice
    {
        return $this->model->where('invoice_number', $invoiceNumber)->first();
    }

    /**
     * Get invoices for a specific vendor.
     */
    public function getByVendor(int $vendorId): Collection
    {
        return $this->model->where('vendor_id', $vendorId)->get();
    }

    /**
     * List invoices with filters, sorting, and pagination.
     */
    public function listWithFilters(
        ?InvoiceStatus $status = null,
        ?int $vendorId = null,
        string $sortField = 'created_at',
        string $sortDirection = 'desc',
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->model->newQuery()->with(['vendor', 'creator']);

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($vendorId !== null) {
            $query->where('vendor_id', $vendorId);
        }

        // Validate and apply sorting
        if (in_array($sortField, self::ALLOWED_SORT_FIELDS, true)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }

        return $query->paginate($perPage);
    }

    /**
     * Find invoice with specified relations loaded.
     */
    public function findWithRelations(int $id, array $relations = []): ?Invoice
    {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }
}
