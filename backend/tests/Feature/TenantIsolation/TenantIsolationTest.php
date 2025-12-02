<?php

declare(strict_types=1);

namespace Tests\Feature\TenantIsolation;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected Organization $otherOrganization;

    protected User $user;

    protected User $otherUser;

    protected Vendor $vendor;

    protected Vendor $otherVendor;

    protected function setUp(): void
    {
        parent::setUp();

        // Create first organization with user and vendor
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Admin,
        ]);
        $this->vendor = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        // Create second organization with user and vendor
        $this->otherOrganization = Organization::factory()->create();
        $this->otherUser = User::factory()->create([
            'organization_id' => $this->otherOrganization->id,
            'role' => UserRole::Admin,
        ]);
        $this->otherVendor = Vendor::factory()->create([
            'organization_id' => $this->otherOrganization->id,
        ]);
    }

    // =========================================================================
    // Invoice Isolation Tests
    // =========================================================================

    public function test_user_can_only_see_own_organization_invoices(): void
    {
        // Create invoices for both organizations
        Invoice::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
        ]);

        Invoice::factory()->count(5)->create([
            'organization_id' => $this->otherOrganization->id,
            'vendor_id' => $this->otherVendor->id,
        ]);

        // User should only see their own organization's invoices
        $response = $this->actingAs($this->user)
            ->getJson('/api/invoices');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_cannot_view_other_organization_invoice(): void
    {
        $otherInvoice = Invoice::factory()->create([
            'organization_id' => $this->otherOrganization->id,
            'vendor_id' => $this->otherVendor->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/invoices/{$otherInvoice->id}");

        $response->assertNotFound();
    }

    public function test_user_cannot_update_other_organization_invoice(): void
    {
        $otherInvoice = Invoice::factory()->create([
            'organization_id' => $this->otherOrganization->id,
            'vendor_id' => $this->otherVendor->id,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/invoices/{$otherInvoice->id}", [
                'subtotal' => 5000.00,
            ]);

        $response->assertNotFound();
    }

    public function test_user_cannot_delete_other_organization_invoice(): void
    {
        $otherInvoice = Invoice::factory()->create([
            'organization_id' => $this->otherOrganization->id,
            'vendor_id' => $this->otherVendor->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/invoices/{$otherInvoice->id}");

        $response->assertNotFound();
    }

    public function test_user_cannot_change_status_of_other_organization_invoice(): void
    {
        $otherInvoice = Invoice::factory()->create([
            'organization_id' => $this->otherOrganization->id,
            'vendor_id' => $this->otherVendor->id,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/invoices/{$otherInvoice->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertNotFound();
    }

    // =========================================================================
    // Vendor Isolation Tests
    // =========================================================================

    public function test_user_can_only_see_own_organization_vendors(): void
    {
        // Create additional vendors for both organizations
        Vendor::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
        ]);

        Vendor::factory()->count(4)->create([
            'organization_id' => $this->otherOrganization->id,
        ]);

        // User should only see their own organization's vendors (1 from setup + 2 new)
        $response = $this->actingAs($this->user)
            ->getJson('/api/vendors');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_cannot_view_other_organization_vendor(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/vendors/{$this->otherVendor->id}");

        $response->assertNotFound();
    }

    public function test_user_cannot_update_other_organization_vendor(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson("/api/vendors/{$this->otherVendor->id}", [
                'name' => 'Hacked Vendor Name',
            ]);

        $response->assertNotFound();
    }

    public function test_user_cannot_delete_other_organization_vendor(): void
    {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/vendors/{$this->otherVendor->id}");

        $response->assertNotFound();
    }

    // =========================================================================
    // Cross-Organization Reference Tests
    // =========================================================================

    public function test_user_cannot_create_invoice_with_other_organization_vendor(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/invoices', [
                'vendor_id' => $this->otherVendor->id, // Other org's vendor
                'invoice_number' => 'INV-HACK-001',
                'invoice_date' => now()->toDateString(),
                'subtotal' => 1000.00,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['vendor_id']);
    }

    // =========================================================================
    // Tenant Context Tests
    // =========================================================================

    public function test_created_invoice_belongs_to_user_organization(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/invoices', [
                'vendor_id' => $this->vendor->id,
                'invoice_number' => 'INV-AUTO-ORG',
                'invoice_date' => now()->toDateString(),
                'subtotal' => 1000.00,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-AUTO-ORG',
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_created_vendor_belongs_to_user_organization(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/vendors', [
                'name' => 'New Vendor',
                'email' => 'newvendor@example.com',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('vendors', [
            'name' => 'New Vendor',
            'organization_id' => $this->organization->id,
        ]);
    }
}
