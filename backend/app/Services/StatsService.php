<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Vendor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatsService
{
    private const CACHE_VERSION_KEY = 'org_{organization_id}_stats_version';
    private const CACHE_KEY = 'user_{user_id}_org_{organization_id}_stats_v{version}';

    /**
     * Get dashboard statistics for an organization, with optional caching.
     */
    public function getDashboardStats(int $organizationId, int $userId): array
    {
        if (!config('cache.dashboard_stats_enabled', false)) {
            return $this->computeStats($organizationId);
        }

        $version = $this->getCacheVersion($organizationId);
        $cacheKey = $this->getCacheKey($userId, $organizationId, $version);
        $ttl = config('cache.dashboard_stats_ttl', 120);

        return Cache::remember($cacheKey, $ttl, function () use ($organizationId) {
            return $this->computeStats($organizationId);
        });
    }

    /**
     * Compute statistics for an organization.
     * Excludes rejected and deleted invoices from total calculations.
     */
    private function computeStats(int $organizationId): array
    {
        $pendingStatus = InvoiceStatus::Pending->value;
        $approvedStatus = InvoiceStatus::Approved->value;
        $paidStatus = InvoiceStatus::Paid->value;
        $rejectedStatus = InvoiceStatus::Rejected->value;

        // Get invoice statistics
        // Exclude rejected and deleted (soft-deleted) invoices from totals
        $invoiceStats = Invoice::query()
            ->where('organization_id', $organizationId)
            ->whereNull('deleted_at')
            ->where('status', '!=', $rejectedStatus)
            ->selectRaw(
                'COUNT(*) as total_count,
                COALESCE(SUM(total_amount), 0) as total_amount,
                COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as pending_count,
                COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as approved_count,
                COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as paid_count,
                COALESCE(SUM(CASE WHEN status = ? THEN total_amount ELSE 0 END), 0) as paid_amount',
                [$pendingStatus, $approvedStatus, $paidStatus, $paidStatus]
            )
            ->first();

        // Count rejected invoices separately (for reference, but not in total)
        $rejectedCount = Invoice::query()
            ->where('organization_id', $organizationId)
            ->whereNull('deleted_at')
            ->where('status', $rejectedStatus)
            ->count();

        // Count deleted invoices separately (for reference)
        $deletedCount = Invoice::query()
            ->where('organization_id', $organizationId)
            ->onlyTrashed()
            ->count();

        // Get vendor statistics
        $vendorStats = Vendor::query()
            ->where('organization_id', $organizationId)
            ->selectRaw(
                'COUNT(*) as total_count,
                COALESCE(SUM(CASE WHEN is_active = ? THEN 1 ELSE 0 END), 0) as active_count',
                [true]
            )
            ->first();

        return [
            'invoices' => [
                'total_count' => (int) $invoiceStats->total_count,
                'total_amount' => (float) $invoiceStats->total_amount,
                'pending_count' => (int) $invoiceStats->pending_count,
                'approved_count' => (int) $invoiceStats->approved_count,
                'paid_count' => (int) $invoiceStats->paid_count,
                'paid_amount' => (float) $invoiceStats->paid_amount,
                'rejected_count' => (int) $rejectedCount,
                'deleted_count' => (int) $deletedCount,
            ],
            'vendors' => [
                'total_count' => (int) $vendorStats->total_count,
                'active_count' => (int) $vendorStats->active_count,
            ],
            'organization_id' => $organizationId,
            'cached_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Invalidate cache for an organization's statistics.
     */
    public function invalidateCache(int $organizationId): void
    {
        $versionKey = str_replace('{organization_id}', (string) $organizationId, self::CACHE_VERSION_KEY);
        Cache::increment($versionKey);
    }

    /**
     * Get the current cache version for an organization.
     */
    private function getCacheVersion(int $organizationId): int
    {
        $versionKey = str_replace('{organization_id}', (string) $organizationId, self::CACHE_VERSION_KEY);
        return (int) Cache::get($versionKey, 1);
    }

    /**
     * Get the cache key for a user's organization stats.
     */
    private function getCacheKey(int $userId, int $organizationId, int $version): string
    {
        return str_replace(
            ['{user_id}', '{organization_id}', '{version}'],
            [(string) $userId, (string) $organizationId, (string) $version],
            self::CACHE_KEY
        );
    }
}
