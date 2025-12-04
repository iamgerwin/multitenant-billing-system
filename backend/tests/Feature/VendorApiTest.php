<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorApiTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $adminUser;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();

        $this->adminUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Admin,
        ]);

        $this->regularUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::User,
        ]);
    }

    public function test_user_can_list_vendors(): void
    {
        Vendor::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/vendors');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_view_vendor(): void
    {
        $vendor = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson("/api/vendors/{$vendor->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $vendor->id);
    }

    public function test_admin_can_create_vendor(): void
    {
        $vendorData = [
            'name' => 'Test Vendor',
            'code' => 'VND-TEST',
            'email' => 'vendor@example.com',
            'phone' => '+1234567890',
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/vendors', $vendorData);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Test Vendor');

        $this->assertDatabaseHas('vendors', [
            'name' => 'Test Vendor',
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_admin_can_update_vendor(): void
    {
        $vendor = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->putJson("/api/vendors/{$vendor->id}", [
                'name' => 'Updated Vendor Name',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Vendor Name');
    }

    public function test_admin_can_delete_vendor(): void
    {
        $vendor = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/vendors/{$vendor->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Vendor deleted successfully.');

        $this->assertSoftDeleted('vendors', ['id' => $vendor->id]);
    }

    public function test_user_cannot_access_other_organization_vendors(): void
    {
        $otherOrg = Organization::factory()->create();
        $vendor = Vendor::factory()->create([
            'organization_id' => $otherOrg->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson("/api/vendors/{$vendor->id}");

        $response->assertNotFound();
    }

    public function test_regular_user_cannot_create_vendor(): void
    {
        $vendorData = [
            'name' => 'Test Vendor',
            'code' => 'VND-TEST',
            'email' => 'vendor@example.com',
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/vendors', $vendorData);

        $response->assertForbidden();
    }
}
