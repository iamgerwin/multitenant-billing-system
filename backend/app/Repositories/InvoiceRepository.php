<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\InvoiceRepositoryInterface;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;

class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{
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
}
