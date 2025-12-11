<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VendorService
{
    public function __construct(
        private VendorRepositoryInterface $repository
    ) {}

    /**
     * List vendors with pagination.
     */
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Create a new vendor.
     */
    public function create(array $data, int $organizationId): Vendor
    {
        $data['organization_id'] = $organizationId;

        return $this->repository->create($data);
    }

    /**
     * Get vendor details with statistics.
     */
    public function show(Vendor $vendor): Vendor
    {
        return $this->repository->findWithStats($vendor->id) ?? $vendor;
    }

    /**
     * Update an existing vendor.
     */
    public function update(Vendor $vendor, array $data): Vendor
    {
        return $this->repository->update($vendor, $data);
    }

    /**
     * Delete a vendor.
     *
     * @return array{success: bool, message: string}
     */
    public function delete(Vendor $vendor): array
    {
        if ($this->repository->hasInvoices($vendor)) {
            return [
                'success' => false,
                'message' => 'Cannot delete vendor with existing invoices.',
            ];
        }

        $this->repository->delete($vendor);

        return [
            'success' => true,
            'message' => 'Vendor deleted successfully.',
        ];
    }

    /**
     * Check if vendor can be deleted.
     */
    public function canDelete(Vendor $vendor): bool
    {
        return ! $this->repository->hasInvoices($vendor);
    }
}
