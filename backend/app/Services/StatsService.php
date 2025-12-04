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
     * Cache TTL in seconds (2 minutes).
     */
    private const CACHE_TTL = 120;

    /**
     * Get dashboard stats for the current organization.
     *
     * @param int $organizationId
     * @return array<string, mixed>
     */
    public function getDashboardStats(int $organizationId): array
    {
        $cacheKey = "org_{$organizationId}_dashboard_stats";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($organizationId) {
            return $this->computeStats($organizationId);
        });
    }

    /**
     * Invalidate cached stats for an organization.
     *
     * @param int $organizationId
     */
    public function invalidateCache(int $organizationId): void
    {
        Cache::forget("org_{$organizationId}_dashboard_stats");
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
            'cached_at' => now()->toIso8601String(),
        ];
    }
}
