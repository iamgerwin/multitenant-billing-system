<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Accountant = 'accountant';
    case User = 'user';

    /**
     * Get the human-readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Accountant => 'Accountant',
            self::User => 'User',
        };
    }

    /**
     * Check if the role has write permissions.
     */
    public function canWrite(): bool
    {
        return match ($this) {
            self::Admin, self::Accountant => true,
            self::User => false,
        };
    }

    /**
     * Check if the role can approve invoices.
     */
    public function canApprove(): bool
    {
        return match ($this) {
            self::Admin => true,
            self::Accountant, self::User => false,
        };
    }

    /**
     * Check if the role can manage users.
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
