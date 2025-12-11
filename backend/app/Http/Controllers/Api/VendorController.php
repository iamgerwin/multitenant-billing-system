<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreVendorRequest;
use App\Http\Requests\Vendor\UpdateVendorRequest;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use App\Services\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class VendorController extends Controller
{
    public function __construct(
        private VendorService $vendorService
    ) {}

    /**
     * Display a listing of vendors.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $vendors = $this->vendorService->list(
            $request->integer('per_page', 15)
        );

        return VendorResource::collection($vendors);
    }

    /**
     * Store a newly created vendor.
     */
    public function store(StoreVendorRequest $request): JsonResponse
    {
        $vendor = $this->vendorService->create(
            $request->validated(),
            $request->user()->organization_id
        );

        return response()->json([
            'message' => 'Vendor created successfully.',
            'data' => new VendorResource($vendor),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified vendor.
     */
    public function show(Vendor $vendor): JsonResponse
    {
        $vendor = $this->vendorService->show($vendor);

        return response()->json([
            'data' => new VendorResource($vendor),
        ]);
    }

    /**
     * Update the specified vendor.
     */
    public function update(UpdateVendorRequest $request, Vendor $vendor): JsonResponse
    {
        $vendor = $this->vendorService->update($vendor, $request->validated());

        return response()->json([
            'message' => 'Vendor updated successfully.',
            'data' => new VendorResource($vendor),
        ]);
    }

    /**
     * Remove the specified vendor.
     */
    public function destroy(Vendor $vendor): JsonResponse
    {
        $result = $this->vendorService->delete($vendor);

        if (! $result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'message' => $result['message'],
        ]);
    }
}
