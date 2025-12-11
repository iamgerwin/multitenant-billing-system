<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\OrganizationRepositoryInterface;
use App\Models\Organization;
use App\Models\User;

class OrganizationService
{
    public function __construct(
        private OrganizationRepositoryInterface $repository
    ) {}

    /**
     * Get the current user's organization with relationship counts.
     */
    public function getCurrentOrganization(User $user): Organization
    {
        $organization = $this->repository->findWithCounts($user->organization_id);

        return $organization ?? $user->organization;
    }
}
