<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\InvoiceRepositoryInterface;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class InvoiceService
{
    public function __construct(
        private InvoiceRepositoryInterface $repository,
        private InvoiceNumberGenerator $numberGenerator
    ) {}

    /**
     * List invoices with filters and pagination.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $status = null;
        if ($request->has('status') && $request->input('status')) {
            $status = InvoiceStatus::tryFrom($request->input('status'));
        }

        $vendorId = null;
        if ($request->has('vendor_id') && $request->input('vendor_id')) {
            $vendorId = $request->integer('vendor_id');
        }

        $sort = $request->input('sort', '-created_at');
        $sortDirection = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $sortField = ltrim($sort, '-');

        return $this->repository->listWithFilters(
            status: $status,
            vendorId: $vendorId,
            sortField: $sortField,
            sortDirection: $sortDirection,
            perPage: $request->integer('per_page', 15)
        );
    }

    /**
     * Generate a unique invoice number.
     */
    public function generateInvoiceNumber(Organization $organization): string
    {
        return $this->numberGenerator->generate($organization);
    }

    /**
     * Create a new invoice.
     */
    public function create(array $data, User $user): Invoice
    {
        $data['organization_id'] = $user->organization_id;
        $data['created_by'] = $user->id;
        $data['status'] = InvoiceStatus::Pending;

        // Auto-generate invoice number if not provided
        if (empty($data['invoice_number'])) {
            $data['invoice_number'] = $this->numberGenerator->generate($user->organization);
        }

        // Calculate total amount
        $data['total_amount'] = $this->calculateTotal(
            $data['subtotal'] ?? 0,
            $data['tax_amount'] ?? 0,
            $data['discount_amount'] ?? 0
        );

        $invoice = $this->repository->create($data);
        $invoice->load(['vendor', 'creator']);

        return $invoice;
    }

    /**
     * Get invoice details with relations.
     */
    public function show(Invoice $invoice): Invoice
    {
        $invoice->load(['vendor', 'creator', 'approver']);

        return $invoice;
    }

    /**
     * Update an existing invoice.
     */
    public function update(Invoice $invoice, array $data): Invoice
    {
        // Recalculate total if amounts changed
        if (isset($data['subtotal']) || isset($data['tax_amount']) || isset($data['discount_amount'])) {
            $subtotal = $data['subtotal'] ?? $invoice->subtotal;
            $taxAmount = $data['tax_amount'] ?? $invoice->tax_amount;
            $discountAmount = $data['discount_amount'] ?? $invoice->discount_amount;
            $data['total_amount'] = $this->calculateTotal($subtotal, $taxAmount, $discountAmount);
        }

        $this->repository->update($invoice, $data);
        $invoice->load(['vendor', 'creator', 'approver']);

        return $invoice->fresh();
    }

    /**
     * Update invoice status.
     */
    public function updateStatus(Invoice $invoice, InvoiceStatus $newStatus, User $user, ?array $paymentData = null): bool
    {
        return match ($newStatus) {
            InvoiceStatus::Approved => $invoice->approve($user),
            InvoiceStatus::Rejected => $invoice->reject(),
            InvoiceStatus::Paid => $invoice->markAsPaid(
                $paymentData['payment_method'] ?? null,
                $paymentData['payment_reference'] ?? null
            ),
            InvoiceStatus::Pending => $invoice->update(['status' => InvoiceStatus::Pending]),
        };
    }

    /**
     * Delete an invoice.
     */
    public function delete(Invoice $invoice): bool
    {
        if (! $invoice->canDelete()) {
            return false;
        }

        return $this->repository->delete($invoice);
    }

    /**
     * Check if an invoice can be deleted.
     */
    public function canDelete(Invoice $invoice): bool
    {
        return $invoice->canDelete();
    }

    /**
     * Calculate total amount from components.
     */
    public function calculateTotal(float $subtotal, float $taxAmount, float $discountAmount): float
    {
        return $subtotal + $taxAmount - $discountAmount;
    }
}
