<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use App\Services\VendorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected VendorService $vendorService;

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

        $this->vendorService = app(VendorService::class);
    }

    public function test_list_returns_paginated_vendors(): void
    {
        $this->actingAs($this->user);

        Vendor::factory()->count(5)->create([
            'organization_id' => $this->organization->id,
        ]);

        $result = $this->vendorService->list(10);

        $this->assertCount(5, $result->items());
    }

    public function test_create_vendor_with_organization_id(): void
    {
        $this->actingAs($this->user);

        $data = [
            'name' => 'Test Vendor',
            'code' => 'TV001',
            'email' => 'vendor@test.com',
            'is_active' => true,
        ];

        $vendor = $this->vendorService->create($data, $this->organization->id);

        $this->assertEquals('Test Vendor', $vendor->name);
        $this->assertEquals('TV001', $vendor->code);
        $this->assertEquals($this->organization->id, $vendor->organization_id);
    }

    public function test_show_returns_vendor_with_stats(): void
    {
        $this->actingAs($this->user);

        $vendor = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        Invoice::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $vendor->id,
            'created_by' => $this->user->id,
            'status' => 'pending',
            'total_amount' => 100,
        ]);

        Invoice::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $vendor->id,
            'created_by' => $this->user->id,
            'status' => 'paid',
            'total_amount' => 200,
        ]);

        $result = $this->vendorService->show($vendor);

        $this->assertEquals(5, $result->invoices_count);
        $this->assertEquals(700, $result->invoices_sum_total_amount);
        $this->assertEquals(3, $result->pending_invoices_count);
    }

    public function test_update_vendor(): void
    {
        $this->actingAs($this->user);

        $vendor = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Old Name',
        ]);

        $data = ['name' => 'New Name'];
        $result = $this->vendorService->update($vendor, $data);

        $this->assertEquals('New Name', $result->name);
    }

    public function test_delete_vendor_without_invoices(): void
    {
        $this->actingAs($this->user);

        $vendor = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $result = $this->vendorService->delete($vendor);

        $this->assertTrue($result['success']);
        $this->assertEquals('Vendor deleted successfully.', $result['message']);
        $this->assertSoftDeleted('vendors', ['id' => $vendor->id]);
    }

    public function test_delete_vendor_with_invoices_returns_error(): void
    {
        $this->actingAs($this->user);

        $vendor = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $vendor->id,
            'created_by' => $this->user->id,
        ]);

        $result = $this->vendorService->delete($vendor);

        $this->assertFalse($result['success']);
        $this->assertEquals('Cannot delete vendor with existing invoices.', $result['message']);
        $this->assertDatabaseHas('vendors', ['id' => $vendor->id, 'deleted_at' => null]);
    }

    public function test_can_delete_returns_correct_value(): void
    {
        $this->actingAs($this->user);

        $vendorWithoutInvoices = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $vendorWithInvoices = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $vendorWithInvoices->id,
            'created_by' => $this->user->id,
        ]);

        $this->assertTrue($this->vendorService->canDelete($vendorWithoutInvoices));
        $this->assertFalse($this->vendorService->canDelete($vendorWithInvoices));
    }
}
