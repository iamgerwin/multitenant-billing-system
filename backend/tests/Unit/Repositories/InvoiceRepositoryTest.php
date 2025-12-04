<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\InvoiceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceRepository $repository;

    private Organization $organization;

    private Vendor $vendor;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InvoiceRepository(new Invoice());

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->forOrganization($this->organization)->admin()->create();
        $this->vendor = Vendor::factory()->forOrganization($this->organization)->create();
    }

    public function test_get_by_status_returns_filtered_invoices(): void
    {
        $this->actingAs($this->user);

        Invoice::factory()->count(3)->forVendor($this->vendor)->pending()->create();
        Invoice::factory()->count(2)->forVendor($this->vendor)->approved()->create();

        $result = $this->repository->getByStatus(InvoiceStatus::Pending);

        $this->assertCount(3, $result);
    }

    public function test_get_pending_returns_pending_invoices(): void
    {
        $this->actingAs($this->user);

        Invoice::factory()->count(2)->forVendor($this->vendor)->pending()->create();
        Invoice::factory()->forVendor($this->vendor)->approved()->create();

        $result = $this->repository->getPending();

        $this->assertCount(2, $result);
    }

    public function test_get_overdue_returns_overdue_invoices(): void
    {
        $this->actingAs($this->user);

        Invoice::factory()->forVendor($this->vendor)->overdue()->create();
        Invoice::factory()->forVendor($this->vendor)->pending()->create();

        $result = $this->repository->getOverdue();

        $this->assertCount(1, $result);
    }

    public function test_find_by_invoice_number_returns_invoice(): void
    {
        $this->actingAs($this->user);

        $invoice = Invoice::factory()->forVendor($this->vendor)->create([
            'invoice_number' => 'INV-TEST-001',
        ]);

        $result = $this->repository->findByInvoiceNumber('INV-TEST-001');

        $this->assertNotNull($result);
        $this->assertEquals($invoice->id, $result->id);
    }

    public function test_find_by_invoice_number_returns_null_when_not_found(): void
    {
        $this->actingAs($this->user);

        $result = $this->repository->findByInvoiceNumber('NON-EXISTENT');

        $this->assertNull($result);
    }

    public function test_get_by_vendor_returns_vendor_invoices(): void
    {
        $this->actingAs($this->user);

        $otherVendor = Vendor::factory()->forOrganization($this->organization)->create();

        Invoice::factory()->count(3)->forVendor($this->vendor)->create();
        Invoice::factory()->count(2)->forVendor($otherVendor)->create();

        $result = $this->repository->getByVendor($this->vendor->id);

        $this->assertCount(3, $result);
    }

    public function test_get_by_status_respects_organization_scope(): void
    {
        $this->actingAs($this->user);

        // Create invoices in user's organization
        Invoice::factory()->count(2)->forVendor($this->vendor)->pending()->create();

        // Create invoices in another organization
        $otherOrg = Organization::factory()->create();
        $otherVendor = Vendor::factory()->forOrganization($otherOrg)->create();
        Invoice::factory()->count(3)->forVendor($otherVendor)->pending()->create();

        $result = $this->repository->getByStatus(InvoiceStatus::Pending);

        // Should only see own organization's invoices
        $this->assertCount(2, $result);
    }
}
