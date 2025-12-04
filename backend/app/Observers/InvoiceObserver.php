<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\InvoiceStatus;
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
     * Invalidates cache only if status changed or deleted_at changed.
     */
    public function updated(Invoice $invoice): void
    {
        // Check if status or deletion status changed
        $statusChanged = $invoice->isDirty('status');
        $deletionStatusChanged = $invoice->isDirty('deleted_at');

        if ($statusChanged || $deletionStatusChanged) {
            $this->invalidateStats($invoice);
        }
    }

    /**
     * Handle the Invoice "deleted" event (soft delete).
     */
    public function deleted(Invoice $invoice): void
    {
        $this->invalidateStats($invoice);
    }

    /**
     * Handle the Invoice "restored" event (restore from soft delete).
     */
    public function restored(Invoice $invoice): void
    {
        $this->invalidateStats($invoice);
    }

    /**
     * Handle the Invoice "force deleted" event (permanent deletion).
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
