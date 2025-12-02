<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    /**
     * Get the current user's organization.
     */
    public function show(Request $request): JsonResponse
    {
        $organization = $request->user()->organization;

        $organization->loadCount(['users', 'vendors', 'invoices']);

        return response()->json([
            'organization' => new OrganizationResource($organization),
        ]);
    }
}
