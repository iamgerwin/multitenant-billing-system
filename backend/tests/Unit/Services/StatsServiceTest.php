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

        $stats = $this->statsService->getDashboardStats($this->organization->id, $this->user->id);

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

        $stats = $this->statsService->getDashboardStats($this->organization->id, $this->user->id);

        $this->assertEquals(4, $stats['vendors']['total_count']);
        $this->assertEquals(3, $stats['vendors']['active_count']);
    }

    public function test_caches_stats_per_user(): void
    {
        Cache::flush();

        // First call should compute and cache
        $stats1 = $this->statsService->getDashboardStats($this->organization->id, $this->user->id);
        $cachedAt1 = $stats1['cached_at'];

        // Second call should return cached
        $stats2 = $this->statsService->getDashboardStats($this->organization->id, $this->user->id);
        $cachedAt2 = $stats2['cached_at'];

        $this->assertEquals($cachedAt1, $cachedAt2);
    }

    public function test_different_users_have_separate_caches(): void
    {
        $otherUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Admin,
        ]);

        Cache::flush();

        // User 1 gets stats
        $stats1 = $this->statsService->getDashboardStats($this->organization->id, $this->user->id);
        $cachedAt1 = $stats1['cached_at'];

        // Small delay to ensure different timestamp
        usleep(1000);

        // User 2 gets stats - should compute fresh (different cache key)
        $stats2 = $this->statsService->getDashboardStats($this->organization->id, $otherUser->id);
        $cachedAt2 = $stats2['cached_at'];

        // Both should have same data but potentially different cache times
        $this->assertEquals($stats1['invoices']['total_count'], $stats2['invoices']['total_count']);
    }

    public function test_invalidate_cache_increments_version(): void
    {
        Cache::flush();

        // Get initial version (0 when no version exists)
        $versionKey = "org_{$this->organization->id}_stats_version";
        $initialVersion = (int) Cache::get($versionKey, 0);

        // Populate cache for user
        $this->statsService->getDashboardStats($this->organization->id, $this->user->id);

        // Verify old cache key exists
        $oldCacheKey = "user_{$this->user->id}_org_{$this->organization->id}_stats_v{$initialVersion}";
        $this->assertTrue(Cache::has($oldCacheKey));

        // Invalidate cache (increments version)
        $this->statsService->invalidateCache($this->organization->id);

        // Next call should use a new cache key (version incremented)
        $newVersion = (int) Cache::get($versionKey);
        $this->assertEquals($initialVersion + 1, $newVersion);

        // New call should create a new cache entry
        $this->statsService->getDashboardStats($this->organization->id, $this->user->id);
        $newCacheKey = "user_{$this->user->id}_org_{$this->organization->id}_stats_v{$newVersion}";
        $this->assertTrue(Cache::has($newCacheKey));
    }

    public function test_invalidation_affects_all_users_in_organization(): void
    {
        $otherUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Admin,
        ]);

        Cache::flush();

        // Get initial version (0 when no version exists)
        $versionKey = "org_{$this->organization->id}_stats_version";
        $initialVersion = (int) Cache::get($versionKey, 0);

        // Both users get stats
        $this->statsService->getDashboardStats($this->organization->id, $this->user->id);
        $this->statsService->getDashboardStats($this->organization->id, $otherUser->id);

        // Verify both cache keys exist with initial version
        $user1OldKey = "user_{$this->user->id}_org_{$this->organization->id}_stats_v{$initialVersion}";
        $user2OldKey = "user_{$otherUser->id}_org_{$this->organization->id}_stats_v{$initialVersion}";
        $this->assertTrue(Cache::has($user1OldKey));
        $this->assertTrue(Cache::has($user2OldKey));

        // Invalidate cache for organization
        $this->statsService->invalidateCache($this->organization->id);

        // Version should be incremented
        $newVersion = (int) Cache::get($versionKey);
        $this->assertEquals($initialVersion + 1, $newVersion);

        // Both users should get new cache entries
        $this->statsService->getDashboardStats($this->organization->id, $this->user->id);
        $this->statsService->getDashboardStats($this->organization->id, $otherUser->id);

        $user1NewKey = "user_{$this->user->id}_org_{$this->organization->id}_stats_v{$newVersion}";
        $user2NewKey = "user_{$otherUser->id}_org_{$this->organization->id}_stats_v{$newVersion}";
        $this->assertTrue(Cache::has($user1NewKey));
        $this->assertTrue(Cache::has($user2NewKey));
    }

    public function test_returns_zero_values_when_no_data(): void
    {
        // Delete the default vendor created in setUp
        $this->vendor->forceDelete();

        Cache::flush();

        $stats = $this->statsService->getDashboardStats($this->organization->id, $this->user->id);

        $this->assertEquals(0, $stats['invoices']['total_count']);
        $this->assertEquals(0, $stats['invoices']['total_amount']);
        $this->assertEquals(0, $stats['invoices']['pending_count']);
        $this->assertEquals(0, $stats['vendors']['total_count']);
        $this->assertEquals(0, $stats['vendors']['active_count']);
    }

    public function test_stats_are_isolated_per_organization(): void
    {
        // Create invoices for our organization
        Invoice::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
            'total_amount' => 100,
        ]);

        // Create another organization with different data
        $otherOrg = Organization::factory()->create();
        $otherUser = User::factory()->create([
            'organization_id' => $otherOrg->id,
            'role' => UserRole::Admin,
        ]);
        $otherVendor = Vendor::factory()->create([
            'organization_id' => $otherOrg->id,
            'is_active' => true,
        ]);
        Invoice::factory()->count(7)->create([
            'organization_id' => $otherOrg->id,
            'vendor_id' => $otherVendor->id,
            'status' => InvoiceStatus::Paid,
            'total_amount' => 500,
        ]);

        Cache::flush();

        // Get stats for first organization
        $stats1 = $this->statsService->getDashboardStats($this->organization->id, $this->user->id);

        // Get stats for second organization
        $stats2 = $this->statsService->getDashboardStats($otherOrg->id, $otherUser->id);

        // First organization should see 3 pending invoices, total 300
        $this->assertEquals(3, $stats1['invoices']['total_count']);
        $this->assertEquals(300, $stats1['invoices']['total_amount']);
        $this->assertEquals(3, $stats1['invoices']['pending_count']);
        $this->assertEquals(0, $stats1['invoices']['paid_count']);
        $this->assertEquals(1, $stats1['vendors']['total_count']);

        // Second organization should see 7 paid invoices, total 3500
        $this->assertEquals(7, $stats2['invoices']['total_count']);
        $this->assertEquals(3500, $stats2['invoices']['total_amount']);
        $this->assertEquals(0, $stats2['invoices']['pending_count']);
        $this->assertEquals(7, $stats2['invoices']['paid_count']);
        $this->assertEquals(1, $stats2['vendors']['total_count']);
    }

    public function test_stats_isolation_works_without_auth_context(): void
    {
        // Create invoices for our organization
        Invoice::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $this->vendor->id,
            'status' => InvoiceStatus::Pending,
            'total_amount' => 100,
        ]);

        // Create another organization with different data
        $otherOrg = Organization::factory()->create();
        $otherUser = User::factory()->create([
            'organization_id' => $otherOrg->id,
            'role' => UserRole::Admin,
        ]);
        $otherVendor = Vendor::factory()->create([
            'organization_id' => $otherOrg->id,
            'is_active' => true,
        ]);
        Invoice::factory()->count(5)->create([
            'organization_id' => $otherOrg->id,
            'vendor_id' => $otherVendor->id,
            'status' => InvoiceStatus::Paid,
            'total_amount' => 200,
        ]);

        // Logout to remove auth context
        Auth::logout();

        Cache::flush();

        // Stats should still be properly scoped even without auth context
        $stats1 = $this->statsService->getDashboardStats($this->organization->id, $this->user->id);
        $stats2 = $this->statsService->getDashboardStats($otherOrg->id, $otherUser->id);

        // First organization should see only its 2 invoices
        $this->assertEquals(2, $stats1['invoices']['total_count']);
        $this->assertEquals(200, $stats1['invoices']['total_amount']);
        $this->assertEquals(1, $stats1['vendors']['total_count']);

        // Second organization should see only its 5 invoices
        $this->assertEquals(5, $stats2['invoices']['total_count']);
        $this->assertEquals(1000, $stats2['invoices']['total_amount']);
        $this->assertEquals(1, $stats2['vendors']['total_count']);
    }
}
