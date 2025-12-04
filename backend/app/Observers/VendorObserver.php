<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Vendor;
use App\Services\StatsService;

class VendorObserver
{
    public function __construct(
        private StatsService $statsService
    ) {}

    /**
     * Handle the Vendor "created" event.
     */
    public function created(Vendor $vendor): void
    {
        $this->invalidateStats($vendor);
    }

    /**
     * Handle the Vendor "updated" event.
     */
    public function updated(Vendor $vendor): void
    {
        $this->invalidateStats($vendor);
    }

    /**
     * Handle the Vendor "deleted" event.
     */
    public function deleted(Vendor $vendor): void
    {
        $this->invalidateStats($vendor);
    }

    /**
     * Handle the Vendor "restored" event.
     */
    public function restored(Vendor $vendor): void
    {
        $this->invalidateStats($vendor);
    }

    /**
     * Handle the Vendor "force deleted" event.
     */
    public function forceDeleted(Vendor $vendor): void
    {
        $this->invalidateStats($vendor);
    }

    /**
     * Invalidate dashboard stats cache for the vendor's organization.
     */
    private function invalidateStats(Vendor $vendor): void
    {
        $this->statsService->invalidateCache($vendor->organization_id);
    }
}
