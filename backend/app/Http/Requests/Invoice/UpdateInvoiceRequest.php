<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoice;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        if (!$invoice instanceof Invoice) {
            return false;
        }

        return $this->user()?->canWrite() && $invoice->canEdit();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;
        $invoiceId = $this->route('invoice')?->id;

        return [
            'vendor_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('vendors', 'id')
                    ->where('organization_id', $organizationId)
                    ->whereNull('deleted_at'),
            ],
            'invoice_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('invoices', 'invoice_number')
                    ->where('organization_id', $organizationId)
                    ->whereNull('deleted_at')
                    ->ignore($invoiceId),
            ],
            'invoice_date' => ['sometimes', 'required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'subtotal' => ['sometimes', 'required', 'numeric', 'min:0', 'max:999999999.99'],
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
            'vendor_id.exists' => 'The selected vendor is not valid.',
            'invoice_number.unique' => 'An invoice with this number already exists.',
            'invoice_date.date' => 'Please provide a valid invoice date.',
            'due_date.after_or_equal' => 'The due date must be on or after the invoice date.',
            'subtotal.numeric' => 'The subtotal must be a number.',
            'subtotal.min' => 'The subtotal cannot be negative.',
            'currency.size' => 'Currency must be a 3-letter code (e.g., USD, EUR).',
        ];
    }
}
