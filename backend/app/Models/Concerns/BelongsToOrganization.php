<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Organization;
use App\Models\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToOrganization
{
    /**
     * Boot the trait.
     *
     * Registers the global scope and auto-assigns organization_id on creation.
     */
    public static function bootBelongsToOrganization(): void
    {
        // Add global scope for automatic filtering
        static::addGlobalScope(new OrganizationScope());

        // Auto-assign organization_id when creating a new record
        static::creating(function ($model) {
            if (empty($model->organization_id) && Auth::check() && Auth::user()->organization_id) {
                $model->organization_id = Auth::user()->organization_id;
            }
        });
    }

    /**
     * Get the organization that owns this model.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope a query to a specific organization.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $organizationId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organizationId);
    }

    /**
     * Query without the organization scope.
     *
     * Useful for admin operations that need to access all organizations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutOrganizationScope($query)
    {
        return $query->withoutGlobalScope(OrganizationScope::class);
    }

    /**
     * Check if this model belongs to the given organization.
     */
    public function belongsToOrganization(int $organizationId): bool
    {
        return $this->organization_id === $organizationId;
    }

    /**
     * Check if this model belongs to the authenticated user's organization.
     */
    public function belongsToCurrentOrganization(): bool
    {
        if (!Auth::check() || !Auth::user()->organization_id) {
            return false;
        }

        return $this->belongsToOrganization(Auth::user()->organization_id);
    }
}
