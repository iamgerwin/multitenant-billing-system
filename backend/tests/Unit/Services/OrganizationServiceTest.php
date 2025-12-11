<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use App\Services\OrganizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrganizationService $organizationService;

    protected Organization $organization;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Admin,
        ]);

        $this->organizationService = app(OrganizationService::class);
    }

    public function test_get_current_organization_returns_organization_with_counts(): void
    {
        $this->actingAs($this->user);

        // Create additional users
        User::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
        ]);

        // Create vendors
        $vendor = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        Vendor::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
        ]);

        // Create invoices
        Invoice::factory()->count(5)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $vendor->id,
            'created_by' => $this->user->id,
        ]);

        $result = $this->organizationService->getCurrentOrganization($this->user);

        $this->assertEquals($this->organization->id, $result->id);
        $this->assertEquals(3, $result->users_count); // Original user + 2 new
        $this->assertEquals(3, $result->vendors_count);
        $this->assertEquals(5, $result->invoices_count);
    }

    public function test_get_current_organization_returns_zeros_for_empty_organization(): void
    {
        $this->actingAs($this->user);

        $result = $this->organizationService->getCurrentOrganization($this->user);

        $this->assertEquals($this->organization->id, $result->id);
        $this->assertEquals(1, $result->users_count); // Only the test user
        $this->assertEquals(0, $result->vendors_count);
        $this->assertEquals(0, $result->invoices_count);
    }

    public function test_organization_data_is_isolated_per_organization(): void
    {
        // Create another organization with different data first (before acting as)
        $otherOrganization = Organization::factory()->create();
        $otherUser = User::factory()->create([
            'organization_id' => $otherOrganization->id,
            'role' => UserRole::Admin,
        ]);
        User::factory()->count(4)->create([
            'organization_id' => $otherOrganization->id,
        ]);

        // Act as first user to create data for first organization
        $this->actingAs($this->user);

        $vendor1 = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        Invoice::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $vendor1->id,
            'created_by' => $this->user->id,
        ]);

        // Get stats for first organization
        $result1 = $this->organizationService->getCurrentOrganization($this->user);

        // Switch to other user to create data for other organization
        $this->actingAs($otherUser);

        $vendor2 = Vendor::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);
        Invoice::factory()->count(7)->create([
            'organization_id' => $otherOrganization->id,
            'vendor_id' => $vendor2->id,
            'created_by' => $otherUser->id,
        ]);

        // Get stats for second organization
        $result2 = $this->organizationService->getCurrentOrganization($otherUser);

        // First organization
        $this->assertEquals(1, $result1->users_count);
        $this->assertEquals(1, $result1->vendors_count);
        $this->assertEquals(3, $result1->invoices_count);

        // Second organization
        $this->assertEquals(5, $result2->users_count);
        $this->assertEquals(1, $result2->vendors_count);
        $this->assertEquals(7, $result2->invoices_count);
    }
}
