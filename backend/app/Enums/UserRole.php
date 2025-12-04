<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Accountant = 'accountant';

    /**
     * Get the human-readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Accountant => 'Accountant',
        };
    }

    /**
     * Check if the role has write permissions.
     * Only Admin can write (create, update, delete resources).
     * Accountant is read-only.
     */
    public function canWrite(): bool
    {
        return $this === self::Admin;
    }

    /**
     * Check if the role can approve invoices.
     * Only Admin can approve invoices.
     */
    public function canApprove(): bool
    {
        return $this === self::Admin;
    }

    /**
     * Check if the role can manage users.
     * Only Admin can manage users.
     */
    public function canManageUsers(): bool
    {
        return $this === self::Admin;
    }

    /**
     * Get all available roles as an array.
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get roles as options for select inputs.
     */
    public static function options(): array
    {
        return array_map(
            fn (self $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ],
            self::cases()
        );
    }
}
