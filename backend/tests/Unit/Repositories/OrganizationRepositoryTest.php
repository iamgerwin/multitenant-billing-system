<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\OrganizationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrganizationRepository $repository;

    private Organization $organization;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new OrganizationRepository(new Organization);
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Admin,
        ]);
    }

    public function test_find_with_counts_returns_organization_with_relationship_counts(): void
    {
        // Create additional users
        User::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
        ]);

        // Create vendors
        $vendor = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        // Create invoices
        Invoice::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $vendor->id,
            'created_by' => $this->user->id,
        ]);

        $result = $this->repository->findWithCounts($this->organization->id);

        $this->assertNotNull($result);
        $this->assertEquals($this->organization->id, $result->id);
        $this->assertEquals(3, $result->users_count); // 1 admin + 2 users
        $this->assertEquals(1, $result->vendors_count);
        $this->assertEquals(3, $result->invoices_count);
    }

    public function test_find_with_counts_returns_null_for_non_existent_organization(): void
    {
        $result = $this->repository->findWithCounts(99999);

        $this->assertNull($result);
    }

    public function test_find_with_counts_returns_zero_counts_for_empty_organization(): void
    {
        $emptyOrganization = Organization::factory()->create();

        $result = $this->repository->findWithCounts($emptyOrganization->id);

        $this->assertNotNull($result);
        $this->assertEquals(0, $result->users_count);
        $this->assertEquals(0, $result->vendors_count);
        $this->assertEquals(0, $result->invoices_count);
    }
}
