<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'vendor_id' => $this->vendor_id,
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'subtotal' => (float) $this->subtotal,
            'tax_amount' => (float) $this->tax_amount,
            'discount_amount' => (float) $this->discount_amount,
            'total_amount' => (float) $this->total_amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'notes' => $this->notes,
            'paid_date' => $this->paid_date?->toDateString(),
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'is_overdue' => $this->isOverdue(),
            'can_edit' => $this->canEdit(),
            'can_delete' => $this->canDelete(),
            'allowed_transitions' => array_map(
                fn ($status) => $status->value,
                $this->status->allowedTransitions()
            ),
            'created_by' => $this->created_by,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'approver' => new UserResource($this->whenLoaded('approver')),
        ];
    }
}
