<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use App\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function __construct(
        private OrganizationService $organizationService
    ) {}

    /**
     * Get the current user's organization.
     */
    public function show(Request $request): JsonResponse
    {
        $organization = $this->organizationService->getCurrentOrganization(
            $request->user()
        );

        return response()->json([
            'organization' => new OrganizationResource($organization),
        ]);
    }
}
