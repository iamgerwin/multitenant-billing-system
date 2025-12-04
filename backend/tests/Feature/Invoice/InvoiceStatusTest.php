<?php

declare(strict_types=1);

namespace Tests\Feature\Invoice;

use App\Enums\InvoiceStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceStatusTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected User $accountant;

    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();

        $this->admin = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Admin,
        ]);

        // Accountant is read-only, cannot perform write operations
        $this->accountant = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Accountant,
        ]);

        $this->vendor = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    // =========================================================================
    // Admin Status Transitions
    // =========================================================================

    public function test_admin_can_approve_pending_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/invoices/{$invoice->id}/status", [
                'status' => InvoiceStatus::Approved->value,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', InvoiceStatus::Approved->value);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => InvoiceStatus::Approved->value,
            'approved_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_reject_pending_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/invoices/{$invoice->id}/status", [
                'status' => InvoiceStatus::Rejected->value,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', InvoiceStatus::Rejected->value);
    }

    public function test_admin_can_mark_approved_invoice_as_paid(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Approved,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/invoices/{$invoice->id}/status", [
                'status' => InvoiceStatus::Paid->value,
                'payment_method' => 'Bank Transfer',
                'payment_reference' => 'TXN-123456',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', InvoiceStatus::Paid->value)
            ->assertJsonPath('data.payment_method', 'Bank Transfer')
            ->assertJsonPath('data.payment_reference', 'TXN-123456');
    }

    public function test_cannot_transition_rejected_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Rejected,
        ]);

        // Rejected is an end state - cannot transition to any other status
        $response = $this->actingAs($this->admin)
            ->patchJson("/api/invoices/{$invoice->id}/status", [
                'status' => InvoiceStatus::Pending->value,
            ]);

        $response->assertForbidden();
    }

    // =========================================================================
    // Invalid Status Transitions
    // =========================================================================

    public function test_cannot_transition_paid_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Paid,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/invoices/{$invoice->id}/status", [
                'status' => InvoiceStatus::Pending->value,
            ]);

        $response->assertForbidden();
    }

    public function test_cannot_transition_pending_directly_to_paid(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/invoices/{$invoice->id}/status", [
                'status' => InvoiceStatus::Paid->value,
            ]);

        $response->assertForbidden();
    }

    public function test_cannot_approve_already_approved_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Approved,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/invoices/{$invoice->id}/status", [
                'status' => InvoiceStatus::Approved->value,
            ]);

        $response->assertForbidden();
    }

    public function test_cannot_reject_approved_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Approved,
        ]);

        // Approved can only transition to Paid, not Rejected
        $response = $this->actingAs($this->admin)
            ->patchJson("/api/invoices/{$invoice->id}/status", [
                'status' => InvoiceStatus::Rejected->value,
            ]);

        $response->assertForbidden();
    }

    // =========================================================================
    // Role-Based Permissions
    // =========================================================================

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

    public function test_accountant_cannot_reject_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        // Accountants are read-only, so they cannot reject invoices
        $response = $this->actingAs($this->accountant)
            ->patchJson("/api/invoices/{$invoice->id}/status", [
                'status' => InvoiceStatus::Rejected->value,
            ]);

        $response->assertForbidden();
    }

    public function test_accountant_cannot_mark_invoice_as_paid(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Approved,
        ]);

        // Accountants are read-only, so they cannot mark invoices as paid
        $response = $this->actingAs($this->accountant)
            ->patchJson("/api/invoices/{$invoice->id}/status", [
                'status' => InvoiceStatus::Paid->value,
                'payment_method' => 'Bank Transfer',
            ]);

        $response->assertForbidden();
    }

    public function test_accountant_cannot_change_invoice_status(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        // Accountants are read-only
        $response = $this->actingAs($this->accountant)
            ->patchJson("/api/invoices/{$invoice->id}/status", [
                'status' => InvoiceStatus::Approved->value,
            ]);

        $response->assertForbidden();
    }

    // =========================================================================
    // Invoice Edit Restrictions Based on Status
    // =========================================================================

    public function test_can_edit_pending_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/invoices/{$invoice->id}", [
                'subtotal' => 2500.00,
            ]);

        $response->assertOk();
        $this->assertEquals(2500, $response->json('data.subtotal'));
    }

    public function test_can_edit_rejected_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Rejected,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/invoices/{$invoice->id}", [
                'subtotal' => 3000.00,
            ]);

        $response->assertOk();
        $this->assertEquals(3000, $response->json('data.subtotal'));
    }

    public function test_cannot_edit_approved_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Approved,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/invoices/{$invoice->id}", [
                'subtotal' => 5000.00,
            ]);

        $response->assertForbidden();
    }

    public function test_cannot_edit_paid_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Paid,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/invoices/{$invoice->id}", [
                'subtotal' => 5000.00,
            ]);

        $response->assertForbidden();
    }

    // =========================================================================
    // Invoice Delete Restrictions Based on Status
    // =========================================================================

    public function test_can_delete_pending_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/invoices/{$invoice->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Invoice deleted successfully.');
        $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
    }

    public function test_cannot_delete_approved_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Approved,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/invoices/{$invoice->id}");

        $response->assertUnprocessable();
    }

    public function test_cannot_delete_paid_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Paid,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/invoices/{$invoice->id}");

        $response->assertUnprocessable();
    }
}
