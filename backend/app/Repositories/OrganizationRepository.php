<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\OrganizationRepositoryInterface;
use App\Models\Organization;

class OrganizationRepository extends BaseRepository implements OrganizationRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Organization $model)
    {
        parent::__construct($model);
    }

    /**
     * Find organization with relationship counts loaded.
     */
    public function findWithCounts(int $id): ?Organization
    {
        return $this->model->newQuery()
            ->where('id', $id)
            ->withCount(['users', 'vendors', 'invoices'])
            ->first();
    }
}
