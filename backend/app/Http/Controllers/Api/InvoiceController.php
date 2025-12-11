<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceStatusRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {}

    /**
     * Generate a new unique invoice number.
     */
    public function generateNumber(Request $request): JsonResponse
    {
        $organization = $request->user()->organization;
        $invoiceNumber = $this->invoiceService->generateInvoiceNumber($organization);

        return response()->json([
            'invoice_number' => $invoiceNumber,
        ]);
    }

    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $invoices = $this->invoiceService->list($request);

        return InvoiceResource::collection($invoices);
    }

    /**
     * Store a newly created invoice.
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->invoiceService->create(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Invoice created successfully.',
            'data' => new InvoiceResource($invoice),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $invoice = $this->invoiceService->show($invoice);

        return response()->json([
            'data' => new InvoiceResource($invoice),
        ]);
    }

    /**
     * Update the specified invoice.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $invoice = $this->invoiceService->update($invoice, $request->validated());

        return response()->json([
            'message' => 'Invoice updated successfully.',
            'data' => new InvoiceResource($invoice),
        ]);
    }

    /**
     * Update the status of the specified invoice.
     */
    public function updateStatus(UpdateInvoiceStatusRequest $request, Invoice $invoice): JsonResponse
    {
        $newStatus = $request->getStatus();
        $paymentData = [
            'payment_method' => $request->validated('payment_method'),
            'payment_reference' => $request->validated('payment_reference'),
        ];

        $success = $this->invoiceService->updateStatus(
            $invoice,
            $newStatus,
            $request->user(),
            $paymentData
        );

        if (! $success) {
            return response()->json([
                'message' => 'Unable to update invoice status.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $invoice = $this->invoiceService->show($invoice->fresh());

        return response()->json([
            'message' => 'Invoice status updated successfully.',
            'data' => new InvoiceResource($invoice),
        ]);
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        if (! $this->invoiceService->canDelete($invoice)) {
            return response()->json([
                'message' => 'This invoice cannot be deleted in its current status.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->invoiceService->delete($invoice);

        return response()->json([
            'message' => 'Invoice deleted successfully.',
        ]);
    }
}
