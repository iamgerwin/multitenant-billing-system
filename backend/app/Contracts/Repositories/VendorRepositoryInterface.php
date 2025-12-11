<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;

interface VendorRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active vendors.
     */
    public function allActive(): Collection;

    /**
     * Find a vendor by code.
     */
    public function findByCode(string $code): ?Vendor;

    /**
     * Find vendor with invoice statistics loaded.
     */
    public function findWithStats(int $id): ?Vendor;

    /**
     * Check if vendor has any invoices.
     */
    public function hasInvoices(Vendor $vendor): bool;
}
