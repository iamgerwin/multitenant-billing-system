<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\InvoiceStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InvoiceService $invoiceService;

    protected Organization $organization;

    protected User $user;

    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Admin,
        ]);
        $this->vendor = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'is_active' => true,
        ]);

        $this->invoiceService = app(InvoiceService::class);
    }

    public function test_list_returns_paginated_invoices(): void
    {
        $this->actingAs($this->user);

        Invoice::factory()->count(5)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'created_by' => $this->user->id,
        ]);

        $request = new Request(['per_page' => 10]);
        $result = $this->invoiceService->list($request);

        $this->assertCount(5, $result->items());
    }

    public function test_list_filters_by_status(): void
    {
        $this->actingAs($this->user);

        Invoice::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'created_by' => $this->user->id,
            'status' => InvoiceStatus::Pending,
        ]);

        Invoice::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'created_by' => $this->user->id,
            'status' => InvoiceStatus::Approved,
        ]);

        $request = new Request(['status' => 'pending', 'per_page' => 10]);
        $result = $this->invoiceService->list($request);

        $this->assertCount(3, $result->items());
    }

    public function test_list_filters_by_vendor_id(): void
    {
        $this->actingAs($this->user);

        $otherVendor = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        Invoice::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'created_by' => $this->user->id,
        ]);

        Invoice::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $otherVendor->id,
            'created_by' => $this->user->id,
        ]);

        $request = new Request(['vendor_id' => $this->vendor->id, 'per_page' => 10]);
        $result = $this->invoiceService->list($request);

        $this->assertCount(3, $result->items());
    }

    public function test_generate_invoice_number_returns_unique_number(): void
    {
        $number = $this->invoiceService->generateInvoiceNumber($this->organization);

        $this->assertNotEmpty($number);
        $this->assertStringContainsString('-', $number);
    }

    public function test_create_invoice_with_all_data(): void
    {
        $this->actingAs($this->user);

        $data = [
            'vendor_id' => $this->vendor->id,
            'invoice_number' => 'INV-TEST-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 1000.00,
            'tax_amount' => 100.00,
            'discount_amount' => 50.00,
            'description' => 'Test invoice',
        ];

        $invoice = $this->invoiceService->create($data, $this->user);

        $this->assertEquals('INV-TEST-001', $invoice->invoice_number);
        $this->assertEquals(InvoiceStatus::Pending, $invoice->status);
        $this->assertEquals(1050.00, $invoice->total_amount); // 1000 + 100 - 50
        $this->assertEquals($this->user->id, $invoice->created_by);
        $this->assertEquals($this->organization->id, $invoice->organization_id);
    }

    public function test_create_invoice_generates_number_if_not_provided(): void
    {
        $this->actingAs($this->user);

        $data = [
            'vendor_id' => $this->vendor->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 500.00,
            'tax_amount' => 0,
            'discount_amount' => 0,
        ];

        $invoice = $this->invoiceService->create($data, $this->user);

        $this->assertNotEmpty($invoice->invoice_number);
    }

    public function test_show_loads_relations(): void
    {
        $this->actingAs($this->user);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'created_by' => $this->user->id,
        ]);

        $result = $this->invoiceService->show($invoice);

        $this->assertTrue($result->relationLoaded('vendor'));
        $this->assertTrue($result->relationLoaded('creator'));
        $this->assertTrue($result->relationLoaded('approver'));
    }

    public function test_update_recalculates_total(): void
    {
        $this->actingAs($this->user);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'created_by' => $this->user->id,
            'subtotal' => 1000.00,
            'tax_amount' => 100.00,
            'discount_amount' => 50.00,
            'total_amount' => 1050.00,
            'status' => InvoiceStatus::Pending,
        ]);

        $data = [
            'subtotal' => 2000.00,
            'tax_amount' => 200.00,
            'discount_amount' => 100.00,
        ];

        $result = $this->invoiceService->update($invoice, $data);

        $this->assertEquals(2100.00, $result->total_amount); // 2000 + 200 - 100
    }

    public function test_update_status_approves_invoice(): void
    {
        $this->actingAs($this->user);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'created_by' => $this->user->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $result = $this->invoiceService->updateStatus($invoice, InvoiceStatus::Approved, $this->user);

        $this->assertTrue($result);
        $this->assertEquals(InvoiceStatus::Approved, $invoice->fresh()->status);
    }

    public function test_update_status_rejects_invoice(): void
    {
        $this->actingAs($this->user);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'created_by' => $this->user->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $result = $this->invoiceService->updateStatus($invoice, InvoiceStatus::Rejected, $this->user);

        $this->assertTrue($result);
        $this->assertEquals(InvoiceStatus::Rejected, $invoice->fresh()->status);
    }

    public function test_update_status_marks_as_paid(): void
    {
        $this->actingAs($this->user);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'created_by' => $this->user->id,
            'status' => InvoiceStatus::Approved,
            'approved_by' => $this->user->id,
            'approved_at' => now(),
        ]);

        $paymentData = [
            'payment_method' => 'bank_transfer',
            'payment_reference' => 'REF-123',
        ];

        $result = $this->invoiceService->updateStatus($invoice, InvoiceStatus::Paid, $this->user, $paymentData);

        $this->assertTrue($result);

        $fresh = $invoice->fresh();
        $this->assertEquals(InvoiceStatus::Paid, $fresh->status);
        $this->assertEquals('bank_transfer', $fresh->payment_method);
        $this->assertEquals('REF-123', $fresh->payment_reference);
    }

    public function test_delete_returns_true_for_deletable_invoice(): void
    {
        $this->actingAs($this->user);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'created_by' => $this->user->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $result = $this->invoiceService->delete($invoice);

        $this->assertTrue($result);
        $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
    }

    public function test_delete_returns_false_for_non_deletable_invoice(): void
    {
        $this->actingAs($this->user);

        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'created_by' => $this->user->id,
            'status' => InvoiceStatus::Paid,
        ]);

        $result = $this->invoiceService->delete($invoice);

        $this->assertFalse($result);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'deleted_at' => null]);
    }

    public function test_can_delete_returns_correct_value(): void
    {
        $this->actingAs($this->user);

        $pendingInvoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'created_by' => $this->user->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $paidInvoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'created_by' => $this->user->id,
            'status' => InvoiceStatus::Paid,
        ]);

        $this->assertTrue($this->invoiceService->canDelete($pendingInvoice));
        $this->assertFalse($this->invoiceService->canDelete($paidInvoice));
    }

    public function test_calculate_total_computes_correctly(): void
    {
        $total = $this->invoiceService->calculateTotal(1000.00, 100.00, 50.00);

        $this->assertEquals(1050.00, $total);
    }

    public function test_calculate_total_handles_zero_values(): void
    {
        $total = $this->invoiceService->calculateTotal(1000.00, 0, 0);

        $this->assertEquals(1000.00, $total);
    }
}
