<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Organization;

interface OrganizationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find organization with relationship counts loaded.
     */
    public function findWithCounts(int $id): ?Organization;
}
