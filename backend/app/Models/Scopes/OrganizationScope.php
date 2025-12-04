<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class OrganizationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * Automatically filters queries by the authenticated user's organization_id.
     * Only applies when a user is authenticated and has an organization.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check() && Auth::user()->organization_id) {
            $builder->where(
                $model->getTable() . '.organization_id',
                Auth::user()->organization_id
            );
        }
    }
}
