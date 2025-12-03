<script setup lang="ts">
import type { InvoiceCreatePayload, ApiErrorResponse } from '@billing/shared'

definePageMeta({
  middleware: 'auth',
})

const router = useRouter()
const invoiceStore = useInvoiceStore()
const vendorStore = useVendorStore()

const form = reactive<InvoiceCreatePayload>({
  vendor_id: 0,
  invoice_number: '',
  invoice_date: new Date().toISOString().split('T')[0],
  due_date: null,
  subtotal: 0,
  tax_amount: 0,
  discount_amount: 0,
  currency: 'USD',
  description: null,
  notes: null,
})

const errors = reactive<Record<string, string[]>>({})
const isSubmitting = ref(false)
const isLoadingInvoiceNumber = ref(true)

const totalAmount = computed(() => {
  return form.subtotal + (form.tax_amount || 0) - (form.discount_amount || 0)
})

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: form.currency || 'USD',
  }).format(amount)
}

const vendorOptions = computed(() =>
  vendorStore.activeVendors.map((v) => ({ value: v.id, label: v.name }))
)

const currencyOptions = [
  { value: 'USD', label: 'USD - US Dollar' },
  { value: 'EUR', label: 'EUR - Euro' },
  { value: 'GBP', label: 'GBP - British Pound' },
]

const handleSubmit = async () => {
  isSubmitting.value = true
  Object.keys(errors).forEach((key) => delete errors[key])

  try {
    const invoice = await invoiceStore.createInvoice(form)
    if (invoice) {
      router.push(`/invoices/${invoice.id}`)
    }
  } catch (err) {
    const error = err as ApiErrorResponse
    if (error.errors) {
      Object.assign(errors, error.errors)
    }
  } finally {
    isSubmitting.value = false
  }
}

onMounted(async () => {
  const [, invoiceNumber] = await Promise.all([
    vendorStore.fetchVendors(1, 100),
    invoiceStore.generateInvoiceNumber(),
  ])

  if (invoiceNumber) {
    form.invoice_number = invoiceNumber
  }
  isLoadingInvoiceNumber.value = false
})
</script>

<template>
  <div class="max-w-4xl mx-auto">
    <UiHeader
      title="Create Invoice"
      description="Fill in the details below to create a new invoice"
      back-link="/invoices"
      back-label="Back to Invoices"
    />

    <form @submit.prevent="handleSubmit" class="space-y-8">
      <!-- Section 1: Invoice Details -->
      <FormSection
        title="Invoice Details"
        description="Basic information about the invoice"
        theme="primary"
      >
        <template #icon>
          <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
          <FormSelect
            id="vendor_id"
            v-model="form.vendor_id"
            label="Vendor"
            :options="vendorOptions"
            placeholder="Select a vendor"
            :required="true"
            :error="errors.vendor_id"
          />

          <div class="space-y-2">
            <label for="invoice_number" class="block text-sm font-medium text-gray-700">
              Invoice Number
            </label>
            <div class="relative">
              <input
                id="invoice_number"
                v-model="form.invoice_number"
                type="text"
                readonly
                class="block w-full px-4 py-3 rounded-lg border border-gray-300 bg-gray-50 shadow-sm text-gray-600"
              />
              <div v-if="isLoadingInvoiceNumber" class="absolute inset-y-0 right-0 flex items-center pr-4">
                <svg class="animate-spin h-5 w-5 text-primary-500" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
              </div>
            </div>
            <p class="text-xs text-gray-500">Auto-generated invoice number</p>
          </div>

          <FormInput
            id="invoice_date"
            v-model="form.invoice_date"
            label="Invoice Date"
            type="date"
            :required="true"
            :error="errors.invoice_date"
          />

          <FormInput
            id="due_date"
            v-model="form.due_date"
            label="Due Date"
            type="date"
            :error="errors.due_date"
          />
        </div>
      </FormSection>

      <!-- Section 2: Financial Details -->
      <FormSection
        title="Financial Details"
        description="Amount and payment information"
        theme="emerald"
      >
        <template #icon>
          <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-6">
          <FormSelect
            id="currency"
            v-model="form.currency"
            label="Currency"
            :options="currencyOptions"
          />

          <FormInput
            id="subtotal"
            v-model="form.subtotal"
            label="Subtotal"
            type="number"
            prefix="$"
            step="0.01"
            min="0"
            placeholder="0.00"
            :required="true"
            :error="errors.subtotal"
          />

          <FormInput
            id="tax_amount"
            v-model="form.tax_amount"
            label="Tax Amount"
            type="number"
            prefix="$"
            step="0.01"
            min="0"
            placeholder="0.00"
          />

          <FormInput
            id="discount_amount"
            v-model="form.discount_amount"
            label="Discount"
            type="number"
            prefix="$"
            step="0.01"
            min="0"
            placeholder="0.00"
          />
        </div>

        <!-- Total Amount Card -->
        <div class="mt-8 p-6 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Amount</p>
              <p class="text-xs text-gray-400 mt-1">Subtotal + Tax - Discount</p>
            </div>
            <div class="text-right">
              <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(totalAmount) }}</p>
            </div>
          </div>
        </div>
      </FormSection>

      <!-- Section 3: Additional Information -->
      <FormSection
        title="Additional Information"
        description="Description and notes (optional)"
        theme="amber"
      >
        <template #icon>
          <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
        </template>

        <div class="space-y-6">
          <FormTextarea
            id="description"
            v-model="form.description"
            label="Description"
            placeholder="Enter a description for this invoice..."
            hint="This will appear on the invoice"
            :rows="3"
          />

          <FormTextarea
            id="notes"
            v-model="form.notes"
            label="Internal Notes"
            placeholder="Add any internal notes..."
            hint="For internal use only - not visible on the invoice"
            :rows="2"
          />
        </div>
      </FormSection>

      <!-- Actions -->
      <FormActions
        cancel-link="/invoices"
        submit-label="Create Invoice"
        submitting-label="Creating..."
        :is-submitting="isSubmitting"
      />
    </form>
  </div>
</template>
