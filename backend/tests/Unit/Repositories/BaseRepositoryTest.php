<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\VendorRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

class BaseRepositoryTest extends TestCase
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

    public function test_all_returns_collection(): void
    {
        $this->actingAs($this->user);

        Vendor::factory()->count(3)->forOrganization($this->organization)->create();

        $result = $this->repository->all();

        $this->assertCount(3, $result);
    }

    public function test_find_returns_model_when_exists(): void
    {
        $this->actingAs($this->user);

        $vendor = Vendor::factory()->forOrganization($this->organization)->create();

        $result = $this->repository->find($vendor->id);

        $this->assertNotNull($result);
        $this->assertEquals($vendor->id, $result->id);
    }

    public function test_find_returns_null_when_not_exists(): void
    {
        $this->actingAs($this->user);

        $result = $this->repository->find(999);

        $this->assertNull($result);
    }

    public function test_find_or_fail_throws_exception_when_not_exists(): void
    {
        $this->actingAs($this->user);

        $this->expectException(ModelNotFoundException::class);

        $this->repository->findOrFail(999);
    }

    public function test_create_persists_model(): void
    {
        $this->actingAs($this->user);

        $data = [
            'organization_id' => $this->organization->id,
            'name' => 'Test Vendor',
            'code' => 'VND-TEST-001',
            'email' => 'test@vendor.com',
        ];

        $result = $this->repository->create($data);

        $this->assertDatabaseHas('vendors', ['name' => 'Test Vendor']);
        $this->assertEquals('Test Vendor', $result->name);
    }

    public function test_update_modifies_model(): void
    {
        $this->actingAs($this->user);

        $vendor = Vendor::factory()->forOrganization($this->organization)->create(['name' => 'Old Name']);

        $result = $this->repository->update($vendor, ['name' => 'New Name']);

        $this->assertEquals('New Name', $result->name);
        $this->assertDatabaseHas('vendors', ['id' => $vendor->id, 'name' => 'New Name']);
    }

    public function test_delete_removes_model(): void
    {
        $this->actingAs($this->user);

        $vendor = Vendor::factory()->forOrganization($this->organization)->create();

        $result = $this->repository->delete($vendor);

        $this->assertTrue($result);
        $this->assertSoftDeleted('vendors', ['id' => $vendor->id]);
    }

    public function test_paginate_returns_paginated_results(): void
    {
        $this->actingAs($this->user);

        Vendor::factory()->count(20)->forOrganization($this->organization)->create();

        $result = $this->repository->paginate(10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
    }
}
