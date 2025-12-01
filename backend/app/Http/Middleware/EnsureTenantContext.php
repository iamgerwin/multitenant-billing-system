<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    /**
     * Handle an incoming request.
     *
     * Ensures the authenticated user belongs to an organization.
     * Returns 403 if the user has no organization context.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->organization_id) {
            return response()->json([
                'message' => 'No organization context. User must belong to an organization.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Account is inactive. Please contact your administrator.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Load the organization relationship for easy access
        if (!$user->relationLoaded('organization')) {
            $user->load('organization');
        }

        // Check if the organization is active
        if (!$user->organization->is_active) {
            return response()->json([
                'message' => 'Organization is inactive. Please contact support.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
