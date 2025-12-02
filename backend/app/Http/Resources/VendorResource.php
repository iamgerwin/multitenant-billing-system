<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'full_address' => $this->full_address,
            'tax_id' => $this->tax_id,
            'payment_terms' => $this->payment_terms,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'invoices_count' => $this->whenCounted('invoices'),
            'total_invoice_amount' => $this->when(
                $this->relationLoaded('invoices'),
                fn () => $this->total_invoice_amount
            ),
            'pending_invoices_count' => $this->when(
                $this->relationLoaded('invoices'),
                fn () => $this->pending_invoices_count
            ),
        ];
    }
}
