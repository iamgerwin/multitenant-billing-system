<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoice;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceStatusRequest extends FormRequest
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

        $user = $this->user();
        $newStatus = InvoiceStatus::tryFrom($this->input('status', ''));

        if (!$newStatus) {
            return false;
        }

        // Check if transition is valid
        if (!$invoice->canTransitionTo($newStatus)) {
            return false;
        }

        // Only admins can approve invoices
        if ($newStatus === InvoiceStatus::Approved) {
            return $user?->canApprove() ?? false;
        }

        // Users with write access can perform other transitions
        return $user?->canWrite() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(InvoiceStatus::toArray()),
            ],
            'payment_method' => [
                'nullable',
                'string',
                'max:100',
                Rule::requiredIf(fn () => $this->input('status') === InvoiceStatus::Paid->value),
            ],
            'payment_reference' => ['nullable', 'string', 'max:255'],
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
            'status.required' => 'The status is required.',
            'status.in' => 'The selected status is not valid.',
            'payment_method.required' => 'Payment method is required when marking as paid.',
        ];
    }

    /**
     * Get the new status as an enum.
     */
    public function getStatus(): InvoiceStatus
    {
        return InvoiceStatus::from($this->validated('status'));
    }
}
