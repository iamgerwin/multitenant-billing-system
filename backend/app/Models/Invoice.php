<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'vendor_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'description',
        'notes',
        'paid_date',
        'payment_method',
        'payment_reference',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'approved_at' => 'datetime',
        'status' => InvoiceStatus::class,
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the vendor that the invoice belongs to.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the user who created the invoice.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the invoice.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeWithStatus($query, InvoiceStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include pending invoices.
     */
    public function scopePending($query)
    {
        return $query->where('status', InvoiceStatus::Pending);
    }

    /**
     * Scope a query to only include approved invoices.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', InvoiceStatus::Approved);
    }

    /**
     * Scope a query to only include paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', InvoiceStatus::Paid);
    }

    /**
     * Scope a query to only include overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Rejected]);
    }

    /**
     * Check if the invoice is overdue.
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }

        return $this->due_date->isPast() && !$this->status->isFinal();
    }

    /**
     * Check if the invoice can be edited.
     */
    public function canEdit(): bool
    {
        return $this->status->canEdit();
    }

    /**
     * Check if the invoice can be deleted.
     */
    public function canDelete(): bool
    {
        return $this->status->canDelete();
    }

    /**
     * Check if the invoice can transition to the given status.
     */
    public function canTransitionTo(InvoiceStatus $status): bool
    {
        return $this->status->canTransitionTo($status);
    }

    /**
     * Approve the invoice.
     */
    public function approve(User $user): bool
    {
        if (!$this->canTransitionTo(InvoiceStatus::Approved)) {
            return false;
        }

        $this->update([
            'status' => InvoiceStatus::Approved,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Reject the invoice.
     */
    public function reject(): bool
    {
        if (!$this->canTransitionTo(InvoiceStatus::Rejected)) {
            return false;
        }

        $this->update([
            'status' => InvoiceStatus::Rejected,
        ]);

        return true;
    }

    /**
     * Mark the invoice as paid.
     */
    public function markAsPaid(?string $paymentMethod = null, ?string $paymentReference = null): bool
    {
        if (!$this->canTransitionTo(InvoiceStatus::Paid)) {
            return false;
        }

        $this->update([
            'status' => InvoiceStatus::Paid,
            'paid_date' => now(),
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
        ]);

        return true;
    }

    /**
     * Calculate and update the total amount.
     */
    public function calculateTotal(): void
    {
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->save();
    }
}
