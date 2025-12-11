<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;

class VendorRepository extends BaseRepository implements VendorRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Vendor $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all active vendors.
     */
    public function allActive(): Collection
    {
        return $this->model->active()->get();
    }

    /**
     * Find a vendor by code.
     */
    public function findByCode(string $code): ?Vendor
    {
        return $this->model->where('code', $code)->first();
    }

    /**
     * Find vendor with invoice statistics loaded.
     */
    public function findWithStats(int $id): ?Vendor
    {
        return $this->model->newQuery()
            ->where('id', $id)
            ->withCount('invoices')
            ->withSum('invoices', 'total_amount')
            ->withCount(['invoices as pending_invoices_count' => function ($query) {
                $query->where('status', 'pending');
            }])
            ->first();
    }

    /**
     * Check if vendor has any invoices.
     */
    public function hasInvoices(Vendor $vendor): bool
    {
        return $vendor->invoices()->exists();
    }
}
