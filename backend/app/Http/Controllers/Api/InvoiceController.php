<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\InvoiceRepositoryInterface;
use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceStatusRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceNumberGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository,
        private InvoiceNumberGenerator $invoiceNumberGenerator
    ) {}

    /**
     * Generate a new unique invoice number.
     */
    public function generateNumber(Request $request): JsonResponse
    {
        $organization = $request->user()->organization;
        $invoiceNumber = $this->invoiceNumberGenerator->generate($organization);

        return response()->json([
            'invoice_number' => $invoiceNumber,
        ]);
    }

    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Invoice::with(['vendor', 'creator']);

        // Filter by status
        if ($request->has('status') && $request->input('status')) {
            $status = InvoiceStatus::tryFrom($request->input('status'));
            if ($status) {
                $query->where('status', $status);
            }
        }

        // Filter by vendor
        if ($request->has('vendor_id') && $request->input('vendor_id')) {
            $query->where('vendor_id', $request->integer('vendor_id'));
        }

        // Apply sorting (default: -created_at for newest first)
        $sort = $request->input('sort', '-created_at');
        $sortDirection = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $sortField = ltrim($sort, '-');

        // Whitelist sortable fields for security
        $allowedSorts = ['created_at', 'updated_at', 'invoice_date', 'due_date', 'total_amount', 'invoice_number'];
        if (in_array($sortField, $allowedSorts, true)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest(); // Default fallback
        }

        $invoices = $query->paginate($request->integer('per_page', 15));

        return InvoiceResource::collection($invoices);
    }

    /**
     * Store a newly created invoice.
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $data['created_by'] = $request->user()->id;
        $data['status'] = InvoiceStatus::Pending;

        // Auto-generate invoice number if not provided
        if (empty($data['invoice_number'])) {
            $data['invoice_number'] = $this->invoiceNumberGenerator->generate(
                $request->user()->organization
            );
        }

        // Calculate total amount
        $data['total_amount'] = ($data['subtotal'] ?? 0)
            + ($data['tax_amount'] ?? 0)
            - ($data['discount_amount'] ?? 0);

        $invoice = $this->invoiceRepository->create($data);
        $invoice->load(['vendor', 'creator']);

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
        $invoice->load(['vendor', 'creator', 'approver']);

        return response()->json([
            'data' => new InvoiceResource($invoice),
        ]);
    }

    /**
     * Update the specified invoice.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $data = $request->validated();

        // Recalculate total if amounts changed
        if (isset($data['subtotal']) || isset($data['tax_amount']) || isset($data['discount_amount'])) {
            $subtotal = $data['subtotal'] ?? $invoice->subtotal;
            $taxAmount = $data['tax_amount'] ?? $invoice->tax_amount;
            $discountAmount = $data['discount_amount'] ?? $invoice->discount_amount;
            $data['total_amount'] = $subtotal + $taxAmount - $discountAmount;
        }

        $this->invoiceRepository->update($invoice, $data);
        $invoice->load(['vendor', 'creator', 'approver']);

        return response()->json([
            'message' => 'Invoice updated successfully.',
            'data' => new InvoiceResource($invoice->fresh()),
        ]);
    }

    /**
     * Update the status of the specified invoice.
     */
    public function updateStatus(UpdateInvoiceStatusRequest $request, Invoice $invoice): JsonResponse
    {
        $newStatus = $request->getStatus();
        $user = $request->user();

        $success = match ($newStatus) {
            InvoiceStatus::Approved => $invoice->approve($user),
            InvoiceStatus::Rejected => $invoice->reject(),
            InvoiceStatus::Paid => $invoice->markAsPaid(
                $request->validated('payment_method'),
                $request->validated('payment_reference')
            ),
            InvoiceStatus::Pending => $invoice->update(['status' => InvoiceStatus::Pending]),
        };

        if (!$success) {
            return response()->json([
                'message' => 'Unable to update invoice status.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $invoice->load(['vendor', 'creator', 'approver']);

        return response()->json([
            'message' => 'Invoice status updated successfully.',
            'data' => new InvoiceResource($invoice->fresh()),
        ]);
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        if (!$invoice->canDelete()) {
            return response()->json([
                'message' => 'This invoice cannot be deleted in its current status.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->invoiceRepository->delete($invoice);

        return response()->json([
            'message' => 'Invoice deleted successfully.',
        ]);
    }
}
