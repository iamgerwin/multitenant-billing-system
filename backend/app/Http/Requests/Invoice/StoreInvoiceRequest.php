<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoice;

use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->canWrite() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;

        return [
            'vendor_id' => [
                'required',
                'integer',
                Rule::exists('vendors', 'id')
                    ->where('organization_id', $organizationId)
                    ->whereNull('deleted_at'),
            ],
            'invoice_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('invoices', 'invoice_number')
                    ->where('organization_id', $organizationId)
                    ->whereNull('deleted_at'),
            ],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'subtotal' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'tax_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'currency' => ['nullable', 'string', 'size:3'],
            'description' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'vendor_id.required' => 'Please select a vendor.',
            'vendor_id.exists' => 'The selected vendor is not valid.',
            'invoice_number.unique' => 'An invoice with this number already exists.',
            'invoice_date.required' => 'The invoice date is required.',
            'invoice_date.date' => 'Please provide a valid invoice date.',
            'due_date.after_or_equal' => 'The due date must be on or after the invoice date.',
            'subtotal.required' => 'The subtotal is required.',
            'subtotal.numeric' => 'The subtotal must be a number.',
            'subtotal.min' => 'The subtotal cannot be negative.',
            'currency.size' => 'Currency must be a 3-letter code (e.g., USD, EUR).',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'tax_amount' => $this->input('tax_amount', 0),
            'discount_amount' => $this->input('discount_amount', 0),
            'status' => InvoiceStatus::Pending->value,
        ]);
    }
}
