<script setup lang="ts">
import type { InvoiceCreatePayload, ApiErrorResponse } from '~/types'

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

const totalAmount = computed(() => {
  return form.subtotal + (form.tax_amount || 0) - (form.discount_amount || 0)
})

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: form.currency || 'USD',
  }).format(amount)
}

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
  await vendorStore.fetchVendors(1, 100)
})
</script>

<template>
  <div>
    <div class="mb-6">
      <NuxtLink to="/invoices" class="text-sm text-gray-500 hover:text-gray-700">
        &larr; Back to Invoices
      </NuxtLink>
      <h1 class="mt-2 text-2xl font-bold text-gray-900">Create Invoice</h1>
    </div>

    <form class="card p-6" @submit.prevent="handleSubmit">
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <!-- Vendor -->
        <div>
          <label for="vendor_id" class="label">Vendor *</label>
          <select
            id="vendor_id"
            v-model.number="form.vendor_id"
            class="input"
            :class="{ 'border-red-300': errors.vendor_id }"
          >
            <option :value="0" disabled>Select a vendor</option>
            <option v-for="vendor in vendorStore.activeVendors" :key="vendor.id" :value="vendor.id">
              {{ vendor.name }}
            </option>
          </select>
          <p v-if="errors.vendor_id" class="mt-1 text-sm text-red-600">
            {{ errors.vendor_id[0] }}
          </p>
        </div>

        <!-- Invoice Number -->
        <div>
          <label for="invoice_number" class="label">Invoice Number *</label>
          <input
            id="invoice_number"
            v-model="form.invoice_number"
            type="text"
            class="input"
            :class="{ 'border-red-300': errors.invoice_number }"
            placeholder="INV-001"
          />
          <p v-if="errors.invoice_number" class="mt-1 text-sm text-red-600">
            {{ errors.invoice_number[0] }}
          </p>
        </div>

        <!-- Invoice Date -->
        <div>
          <label for="invoice_date" class="label">Invoice Date *</label>
          <input
            id="invoice_date"
            v-model="form.invoice_date"
            type="date"
            class="input"
            :class="{ 'border-red-300': errors.invoice_date }"
          />
          <p v-if="errors.invoice_date" class="mt-1 text-sm text-red-600">
            {{ errors.invoice_date[0] }}
          </p>
        </div>

        <!-- Due Date -->
        <div>
          <label for="due_date" class="label">Due Date</label>
          <input
            id="due_date"
            v-model="form.due_date"
            type="date"
            class="input"
            :class="{ 'border-red-300': errors.due_date }"
          />
          <p v-if="errors.due_date" class="mt-1 text-sm text-red-600">
            {{ errors.due_date[0] }}
          </p>
        </div>

        <!-- Currency -->
        <div>
          <label for="currency" class="label">Currency</label>
          <select
            id="currency"
            v-model="form.currency"
            class="input"
          >
            <option value="USD">USD - US Dollar</option>
            <option value="EUR">EUR - Euro</option>
            <option value="GBP">GBP - British Pound</option>
          </select>
        </div>

        <!-- Subtotal -->
        <div>
          <label for="subtotal" class="label">Subtotal *</label>
          <input
            id="subtotal"
            v-model.number="form.subtotal"
            type="number"
            step="0.01"
            min="0"
            class="input"
            :class="{ 'border-red-300': errors.subtotal }"
          />
          <p v-if="errors.subtotal" class="mt-1 text-sm text-red-600">
            {{ errors.subtotal[0] }}
          </p>
        </div>

        <!-- Tax Amount -->
        <div>
          <label for="tax_amount" class="label">Tax Amount</label>
          <input
            id="tax_amount"
            v-model.number="form.tax_amount"
            type="number"
            step="0.01"
            min="0"
            class="input"
          />
        </div>

        <!-- Discount Amount -->
        <div>
          <label for="discount_amount" class="label">Discount Amount</label>
          <input
            id="discount_amount"
            v-model.number="form.discount_amount"
            type="number"
            step="0.01"
            min="0"
            class="input"
          />
        </div>

        <!-- Total (Calculated) -->
        <div class="sm:col-span-2">
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex justify-between text-lg font-semibold">
              <span>Total Amount:</span>
              <span>{{ formatCurrency(totalAmount) }}</span>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div class="sm:col-span-2">
          <label for="description" class="label">Description</label>
          <textarea
            id="description"
            v-model="form.description"
            rows="3"
            class="input"
            placeholder="Invoice description..."
          />
        </div>

        <!-- Notes -->
        <div class="sm:col-span-2">
          <label for="notes" class="label">Notes</label>
          <textarea
            id="notes"
            v-model="form.notes"
            rows="2"
            class="input"
            placeholder="Internal notes..."
          />
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-6 flex items-center justify-end space-x-4">
        <NuxtLink to="/invoices" class="btn-secondary">
          Cancel
        </NuxtLink>
        <button
          type="submit"
          :disabled="isSubmitting"
          class="btn-primary"
          :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }"
        >
          {{ isSubmitting ? 'Creating...' : 'Create Invoice' }}
        </button>
      </div>
    </form>
  </div>
</template>
