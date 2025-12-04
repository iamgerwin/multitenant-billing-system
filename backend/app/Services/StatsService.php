<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Vendor;
use Illuminate\Support\Facades\Cache;

class StatsService
{
    /**
     * Get dashboard stats for the current user within their organization.
     *
     * @param int $organizationId
     * @param int $userId
     * @return array<string, mixed>
     */
    public function getDashboardStats(int $organizationId, int $userId): array
    {
        // Check if caching is enabled (disabled by default for tenant isolation safety)
        if (!$this->isCacheEnabled()) {
            return $this->computeStats($organizationId);
        }

        $version = $this->getCacheVersion($organizationId);
        $cacheKey = "user_{$userId}_org_{$organizationId}_stats_v{$version}";

        return Cache::remember($cacheKey, $this->getCacheTtl(), function () use ($organizationId) {
            return $this->computeStats($organizationId);
        });
    }

    /**
     * Invalidate cached stats for all users in an organization.
     * Uses version-based invalidation to efficiently invalidate all user caches.
     *
     * @param int $organizationId
     */
    public function invalidateCache(int $organizationId): void
    {
        if (!$this->isCacheEnabled()) {
            return;
        }

        $versionKey = "org_{$organizationId}_stats_version";
        $currentVersion = (int) Cache::get($versionKey, 0);
        Cache::put($versionKey, $currentVersion + 1);
    }

    /**
     * Check if dashboard stats caching is enabled.
     * Disabled by default for maximum tenant isolation safety.
     *
     * @return bool
     */
    private function isCacheEnabled(): bool
    {
        return (bool) config('cache.dashboard_stats_enabled', false);
    }

    /**
     * Get the cache TTL in seconds.
     *
     * @return int
     */
    private function getCacheTtl(): int
    {
        return (int) config('cache.dashboard_stats_ttl', 120);
    }

    /**
     * Get the current cache version for an organization.
     *
     * @param int $organizationId
     * @return int
     */
    private function getCacheVersion(int $organizationId): int
    {
        $versionKey = "org_{$organizationId}_stats_version";

        return (int) Cache::get($versionKey, 0);
    }

    /**
     * Compute stats from database.
     *
     * @param int $organizationId
     * @return array<string, mixed>
     */
    private function computeStats(int $organizationId): array
    {
        // Invoice stats - using aggregation queries for efficiency
        // Explicitly filter by organization_id to ensure multi-tenant isolation
        $invoiceStats = Invoice::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count', [InvoiceStatus::Pending->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved_count', [InvoiceStatus::Approved->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as paid_count', [InvoiceStatus::Paid->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected_count', [InvoiceStatus::Rejected->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN total_amount ELSE 0 END) as paid_amount', [InvoiceStatus::Paid->value])
            ->first();

        // Vendor stats - using parameter binding for database-agnostic boolean comparison
        // Explicitly filter by organization_id to ensure multi-tenant isolation
        $vendorStats = Vendor::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(CASE WHEN is_active = ? THEN 1 ELSE 0 END) as active_count', [true])
            ->first();

        return [
            'invoices' => [
                'total_count' => (int) ($invoiceStats->total_count ?? 0),
                'total_amount' => (float) ($invoiceStats->total_amount ?? 0),
                'pending_count' => (int) ($invoiceStats->pending_count ?? 0),
                'approved_count' => (int) ($invoiceStats->approved_count ?? 0),
                'paid_count' => (int) ($invoiceStats->paid_count ?? 0),
                'rejected_count' => (int) ($invoiceStats->rejected_count ?? 0),
                'paid_amount' => (float) ($invoiceStats->paid_amount ?? 0),
            ],
            'vendors' => [
                'total_count' => (int) ($vendorStats->total_count ?? 0),
                'active_count' => (int) ($vendorStats->active_count ?? 0),
            ],
            'organization_id' => $organizationId,
            'cached_at' => now()->toIso8601String(),
        ];
    }
}
