<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\VendorRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private VendorRepository $repository;

    private Organization $organization;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new VendorRepository(new Vendor());

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->forOrganization($this->organization)->admin()->create();
    }

    public function test_all_active_returns_only_active_vendors(): void
    {
        $this->actingAs($this->user);

        Vendor::factory()->count(3)->forOrganization($this->organization)->create();
        Vendor::factory()->forOrganization($this->organization)->inactive()->create();

        $result = $this->repository->allActive();

        $this->assertCount(3, $result);
    }

    public function test_find_by_code_returns_vendor(): void
    {
        $this->actingAs($this->user);

        $vendor = Vendor::factory()->forOrganization($this->organization)->create([
            'code' => 'VND-TEST-CODE',
        ]);

        $result = $this->repository->findByCode('VND-TEST-CODE');

        $this->assertNotNull($result);
        $this->assertEquals($vendor->id, $result->id);
    }

    public function test_find_by_code_returns_null_when_not_found(): void
    {
        $this->actingAs($this->user);

        $result = $this->repository->findByCode('NON-EXISTENT');

        $this->assertNull($result);
    }

    public function test_all_active_respects_organization_scope(): void
    {
        $this->actingAs($this->user);

        // Create vendors in user's organization
        Vendor::factory()->count(2)->forOrganization($this->organization)->create();

        // Create vendors in another organization
        $otherOrg = Organization::factory()->create();
        Vendor::factory()->count(3)->forOrganization($otherOrg)->create();

        $result = $this->repository->allActive();

        // Should only see own organization's vendors
        $this->assertCount(2, $result);
    }

    public function test_find_by_code_respects_organization_scope(): void
    {
        $this->actingAs($this->user);

        // Create vendor in another organization with a code
        $otherOrg = Organization::factory()->create();
        Vendor::factory()->forOrganization($otherOrg)->create([
            'code' => 'VND-OTHER-ORG',
        ]);

        $result = $this->repository->findByCode('VND-OTHER-ORG');

        // Should not find vendor from other organization
        $this->assertNull($result);
    }
}
