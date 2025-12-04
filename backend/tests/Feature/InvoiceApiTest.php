<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $adminUser;

    protected User $accountant;

    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();

        $this->adminUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Admin,
        ]);

        $this->accountant = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Accountant,
        ]);

        $this->vendor = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_user_can_list_invoices(): void
    {
        Invoice::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/invoices');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_view_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson("/api/invoices/{$invoice->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $invoice->id);
    }

    public function test_admin_can_create_invoice(): void
    {
        $invoiceData = [
            'vendor_id' => $this->vendor->id,
            'invoice_number' => 'INV-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 1000.00,
            'tax_amount' => 100.00,
            'discount_amount' => 50.00,
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/invoices', $invoiceData);

        $response->assertCreated()
            ->assertJsonPath('data.invoice_number', 'INV-001')
            ->assertJsonPath('data.status', InvoiceStatus::Pending->value);

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-001',
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_accountant_cannot_create_invoice(): void
    {
        $invoiceData = [
            'vendor_id' => $this->vendor->id,
            'invoice_number' => 'INV-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 1000.00,
        ];

        // Accountant is read-only, cannot create invoices
        $response = $this->actingAs($this->accountant)
            ->postJson('/api/invoices', $invoiceData);

        $response->assertForbidden();
    }

    public function test_admin_can_approve_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->patchJson("/api/invoices/{$invoice->id}/status", [
                'status' => InvoiceStatus::Approved->value,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', InvoiceStatus::Approved->value);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => InvoiceStatus::Approved->value,
            'approved_by' => $this->adminUser->id,
        ]);
    }

    public function test_accountant_cannot_approve_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $response = $this->actingAs($this->accountant)
            ->patchJson("/api/invoices/{$invoice->id}/status", [
                'status' => InvoiceStatus::Approved->value,
            ]);

        $response->assertForbidden();
    }

    public function test_invoice_can_be_marked_as_paid(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Approved,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->patchJson("/api/invoices/{$invoice->id}/status", [
                'status' => InvoiceStatus::Paid->value,
                'payment_method' => 'Bank Transfer',
                'payment_reference' => 'REF-12345',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', InvoiceStatus::Paid->value)
            ->assertJsonPath('data.payment_method', 'Bank Transfer');
    }

    public function test_cannot_edit_paid_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Paid,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->putJson("/api/invoices/{$invoice->id}", [
                'subtotal' => 2000.00,
            ]);

        $response->assertForbidden();
    }

    public function test_user_cannot_access_other_organization_invoices(): void
    {
        $otherOrg = Organization::factory()->create();
        $otherVendor = Vendor::factory()->create(['organization_id' => $otherOrg->id]);
        $invoice = Invoice::factory()->create([
            'organization_id' => $otherOrg->id,
            'vendor_id' => $otherVendor->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson("/api/invoices/{$invoice->id}");

        $response->assertNotFound();
    }
}
