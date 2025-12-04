<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\InvoiceStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use App\Services\StatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StatsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $user;

    protected Vendor $vendor;

    protected StatsService $statsService;

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

        $this->statsService = new StatsService();

        // Set authenticated user for organization scoping
        Auth::login($this->user);
    }

    public function test_computes_correct_invoice_stats(): void
    {
        Invoice::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
            'total_amount' => 100,
        ]);

        Invoice::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Paid,
            'total_amount' => 500,
        ]);

        Cache::flush();

        $stats = $this->statsService->getDashboardStats($this->organization->id);

        $this->assertEquals(5, $stats['invoices']['total_count']);
        $this->assertEquals(2, $stats['invoices']['pending_count']);
        $this->assertEquals(3, $stats['invoices']['paid_count']);
        $this->assertEquals(1700, $stats['invoices']['total_amount']); // 2*100 + 3*500
        $this->assertEquals(1500, $stats['invoices']['paid_amount']); // 3*500
    }

    public function test_computes_correct_vendor_stats(): void
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

        Cache::flush();

        $stats = $this->statsService->getDashboardStats($this->organization->id);

        $this->assertEquals(4, $stats['vendors']['total_count']);
        $this->assertEquals(3, $stats['vendors']['active_count']);
    }

    public function test_caches_stats(): void
    {
        Cache::flush();

        // First call should compute and cache
        $stats1 = $this->statsService->getDashboardStats($this->organization->id);
        $cachedAt1 = $stats1['cached_at'];

        // Second call should return cached
        $stats2 = $this->statsService->getDashboardStats($this->organization->id);
        $cachedAt2 = $stats2['cached_at'];

        $this->assertEquals($cachedAt1, $cachedAt2);
    }

    public function test_invalidate_cache_clears_cached_stats(): void
    {
        Cache::flush();

        // Populate cache
        $this->statsService->getDashboardStats($this->organization->id);

        // Verify cache exists
        $cacheKey = "org_{$this->organization->id}_dashboard_stats";
        $this->assertTrue(Cache::has($cacheKey));

        // Invalidate
        $this->statsService->invalidateCache($this->organization->id);

        // Verify cache is cleared
        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_returns_zero_values_when_no_data(): void
    {
        // Delete the default vendor created in setUp
        $this->vendor->forceDelete();

        Cache::flush();

        $stats = $this->statsService->getDashboardStats($this->organization->id);

        $this->assertEquals(0, $stats['invoices']['total_count']);
        $this->assertEquals(0, $stats['invoices']['total_amount']);
        $this->assertEquals(0, $stats['invoices']['pending_count']);
        $this->assertEquals(0, $stats['vendors']['total_count']);
        $this->assertEquals(0, $stats['vendors']['active_count']);
    }
}
