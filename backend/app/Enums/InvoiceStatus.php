<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoiceStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Paid = 'paid';

    /**
     * Get the human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Paid => 'Paid',
        };
    }

    /**
     * Get the color associated with the status (for UI).
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Paid => 'info',
        };
    }

    /**
     * Get the allowed transitions from this status.
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Approved, self::Rejected],
            self::Approved => [self::Paid],
            self::Rejected => [],
            self::Paid => [],
        };
    }

    /**
     * Check if the invoice can transition to the given status.
     */
    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    /**
     * Check if the invoice is in a final state.
     */
    public function isFinal(): bool
    {
        return $this === self::Paid;
    }

    /**
     * Check if the invoice can be edited.
     */
    public function canEdit(): bool
    {
        return $this === self::Pending || $this === self::Rejected;
    }

    /**
     * Check if the invoice can be deleted.
     */
    public function canDelete(): bool
    {
        return $this === self::Pending || $this === self::Rejected;
    }

    /**
     * Get all available statuses as an array.
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get statuses as options for select inputs.
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status) => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
            ],
            self::cases()
        );
    }
}
