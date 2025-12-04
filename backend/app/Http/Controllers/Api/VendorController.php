<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreVendorRequest;
use App\Http\Requests\Vendor\UpdateVendorRequest;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class VendorController extends Controller
{
    public function __construct(
        private VendorRepositoryInterface $vendorRepository
    ) {}

    /**
     * Display a listing of vendors.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $vendors = $this->vendorRepository->paginate(
            $request->integer('per_page', 15)
        );

        return VendorResource::collection($vendors);
    }

    /**
     * Store a newly created vendor.
     */
    public function store(StoreVendorRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;

        $vendor = $this->vendorRepository->create($data);

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
        $vendor->loadCount('invoices')
            ->loadSum('invoices', 'total_amount')
            ->loadCount(['invoices as pending_invoices_count' => function ($query) {
                $query->where('status', 'pending');
            }]);

        return response()->json([
            'data' => new VendorResource($vendor),
        ]);
    }

    /**
     * Update the specified vendor.
     */
    public function update(UpdateVendorRequest $request, Vendor $vendor): JsonResponse
    {
        $this->vendorRepository->update($vendor, $request->validated());

        return response()->json([
            'message' => 'Vendor updated successfully.',
            'data' => new VendorResource($vendor->fresh()),
        ]);
    }

    /**
     * Remove the specified vendor.
     */
    public function destroy(Vendor $vendor): JsonResponse
    {
        // Check if vendor has invoices
        if ($vendor->invoices()->exists()) {
            return response()->json([
                'message' => 'Cannot delete vendor with existing invoices.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->vendorRepository->delete($vendor);

        return response()->json([
            'message' => 'Vendor deleted successfully.',
        ]);
    }
}
