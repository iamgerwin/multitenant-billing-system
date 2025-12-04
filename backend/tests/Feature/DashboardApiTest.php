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
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $adminUser;

    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();

        $this->adminUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Admin,
        ]);

        $this->vendor = Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'is_active' => true,
        ]);
    }

    public function test_user_can_get_dashboard_stats(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/dashboard/stats');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'invoices' => [
                        'total_count',
                        'total_amount',
                        'pending_count',
                        'approved_count',
                        'paid_count',
                        'rejected_count',
                        'paid_amount',
                    ],
                    'vendors' => [
                        'total_count',
                        'active_count',
                    ],
                    'cached_at',
                ],
            ]);
    }

    public function test_stats_reflect_correct_invoice_counts(): void
    {
        // Create invoices with various statuses
        Invoice::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
            'total_amount' => 100,
        ]);

        Invoice::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Approved,
            'total_amount' => 200,
        ]);

        Invoice::factory()->count(4)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Paid,
            'total_amount' => 500,
        ]);

        Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Rejected,
            'total_amount' => 50,
        ]);

        // Clear cache to get fresh stats
        Cache::flush();

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/dashboard/stats');

        $response->assertOk()
            ->assertJsonPath('data.invoices.total_count', 10)
            ->assertJsonPath('data.invoices.pending_count', 3)
            ->assertJsonPath('data.invoices.approved_count', 2)
            ->assertJsonPath('data.invoices.paid_count', 4)
            ->assertJsonPath('data.invoices.rejected_count', 1);
    }

    public function test_stats_reflect_correct_total_amounts(): void
    {
        Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
            'total_amount' => 1000.50,
        ]);

        Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Paid,
            'total_amount' => 2500.75,
        ]);

        // Clear cache to get fresh stats
        Cache::flush();

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/dashboard/stats');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals(3501.25, $data['invoices']['total_amount']);
        $this->assertEquals(2500.75, $data['invoices']['paid_amount']);
    }

    public function test_stats_reflect_correct_vendor_counts(): void
    {
        // Already have 1 active vendor from setUp
        Vendor::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'is_active' => true,
        ]);

        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'is_active' => false,
        ]);

        // Clear cache to get fresh stats
        Cache::flush();

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/dashboard/stats');

        $response->assertOk()
            ->assertJsonPath('data.vendors.total_count', 4)
            ->assertJsonPath('data.vendors.active_count', 3);
    }

    public function test_stats_are_tenant_isolated(): void
    {
        // Create invoices for our organization
        Invoice::factory()->count(5)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        // Create another organization with invoices
        $otherOrg = Organization::factory()->create();
        $otherUser = User::factory()->create([
            'organization_id' => $otherOrg->id,
            'role' => UserRole::Admin,
        ]);
        $otherVendor = Vendor::factory()->create([
            'organization_id' => $otherOrg->id,
        ]);
        Invoice::factory()->count(10)->create([
            'organization_id' => $otherOrg->id,
            'vendor_id' => $otherVendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        // Clear cache to get fresh stats
        Cache::flush();

        // Our user should only see their organization's stats
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/dashboard/stats');

        $response->assertOk()
            ->assertJsonPath('data.invoices.total_count', 5);

        // Other user should only see their organization's stats
        $response = $this->actingAs($otherUser)
            ->getJson('/api/dashboard/stats');

        $response->assertOk()
            ->assertJsonPath('data.invoices.total_count', 10);
    }

    public function test_stats_cache_is_invalidated_on_invoice_create(): void
    {
        Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        // Clear cache
        Cache::flush();

        // First request - should populate cache
        $response1 = $this->actingAs($this->adminUser)
            ->getJson('/api/dashboard/stats');

        $response1->assertOk()
            ->assertJsonPath('data.invoices.total_count', 1);

        // Create another invoice - this should invalidate the cache via observer
        Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        // Second request - should reflect new count (cache invalidated by observer)
        $response2 = $this->actingAs($this->adminUser)
            ->getJson('/api/dashboard/stats');

        $response2->assertOk()
            ->assertJsonPath('data.invoices.total_count', 2);
    }

    public function test_stats_cache_is_invalidated_on_invoice_update(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
            'total_amount' => 100,
        ]);

        // Clear cache
        Cache::flush();

        // First request - should populate cache
        $response1 = $this->actingAs($this->adminUser)
            ->getJson('/api/dashboard/stats');

        $response1->assertOk();
        $this->assertEquals(100, $response1->json('data.invoices.total_amount'));

        // Update the invoice - this should invalidate the cache
        $invoice->update(['total_amount' => 500]);

        // Second request - should reflect updated amount
        $response2 = $this->actingAs($this->adminUser)
            ->getJson('/api/dashboard/stats');

        $response2->assertOk();
        $this->assertEquals(500, $response2->json('data.invoices.total_amount'));
    }

    public function test_stats_cache_is_invalidated_on_vendor_create(): void
    {
        // Clear cache
        Cache::flush();

        // First request - should populate cache (1 vendor from setUp)
        $response1 = $this->actingAs($this->adminUser)
            ->getJson('/api/dashboard/stats');

        $response1->assertOk()
            ->assertJsonPath('data.vendors.total_count', 1);

        // Create another vendor - this should invalidate the cache via observer
        Vendor::factory()->create([
            'organization_id' => $this->organization->id,
            'is_active' => true,
        ]);

        // Second request - should reflect new count
        $response2 = $this->actingAs($this->adminUser)
            ->getJson('/api/dashboard/stats');

        $response2->assertOk()
            ->assertJsonPath('data.vendors.total_count', 2);
    }

    public function test_unauthenticated_user_cannot_access_stats(): void
    {
        $response = $this->getJson('/api/dashboard/stats');

        $response->assertUnauthorized();
    }
}
