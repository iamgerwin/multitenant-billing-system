<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Invoice;
use App\Services\StatsService;

class InvoiceObserver
{
    public function __construct(
        private StatsService $statsService
    ) {}

    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        $this->invalidateStats($invoice);
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        $this->invalidateStats($invoice);
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        $this->invalidateStats($invoice);
    }

    /**
     * Handle the Invoice "restored" event.
     */
    public function restored(Invoice $invoice): void
    {
        $this->invalidateStats($invoice);
    }

    /**
     * Handle the Invoice "force deleted" event.
     */
    public function forceDeleted(Invoice $invoice): void
    {
        $this->invalidateStats($invoice);
    }

    /**
     * Invalidate dashboard stats cache for the invoice's organization.
     */
    private function invalidateStats(Invoice $invoice): void
    {
        $this->statsService->invalidateCache($invoice->organization_id);
    }
}
