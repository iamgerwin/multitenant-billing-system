<script setup lang="ts">
import { InvoiceStatus, InvoiceStatusLabels } from '~/types'
import type { InvoiceStatusPayload, ApiErrorResponse } from '~/types'

definePageMeta({
  middleware: 'auth',
})

const route = useRoute()
const router = useRouter()
const invoiceStore = useInvoiceStore()
const authStore = useAuthStore()

const invoiceId = computed(() => Number(route.params.id))
const invoice = computed(() => invoiceStore.currentInvoice)

const showPaymentModal = ref(false)
const paymentForm = reactive({
  payment_method: '',
  payment_reference: '',
})
const isUpdatingStatus = ref(false)

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: invoice.value?.currency || 'USD',
  }).format(amount)
}

const handleStatusUpdate = async (status: InvoiceStatus) => {
  if (status === InvoiceStatus.Paid) {
    showPaymentModal.value = true
    return
  }

  isUpdatingStatus.value = true
  try {
    await invoiceStore.updateInvoiceStatus(invoiceId.value, { status })
  } catch (err) {
    const error = err as ApiErrorResponse
    alert(error.message || 'Failed to update status')
  } finally {
    isUpdatingStatus.value = false
  }
}

const handlePaymentSubmit = async () => {
  isUpdatingStatus.value = true
  try {
    const payload: InvoiceStatusPayload = {
      status: InvoiceStatus.Paid,
      payment_method: paymentForm.payment_method || null,
      payment_reference: paymentForm.payment_reference || null,
    }
    await invoiceStore.updateInvoiceStatus(invoiceId.value, payload)
    showPaymentModal.value = false
    paymentForm.payment_method = ''
    paymentForm.payment_reference = ''
  } catch (err) {
    const error = err as ApiErrorResponse
    alert(error.message || 'Failed to update status')
  } finally {
    isUpdatingStatus.value = false
  }
}

const handleDelete = async () => {
  if (confirm('Are you sure you want to delete this invoice?')) {
    const success = await invoiceStore.deleteInvoice(invoiceId.value)
    if (success) {
      router.push('/invoices')
    }
  }
}

onMounted(async () => {
  await invoiceStore.fetchInvoice(invoiceId.value)
})

onUnmounted(() => {
  invoiceStore.clearCurrentInvoice()
})
</script>

<template>
  <div>
    <div class="mb-6">
      <NuxtLink to="/invoices" class="text-sm text-gray-500 hover:text-gray-700">
        &larr; Back to Invoices
      </NuxtLink>
    </div>

    <!-- Loading -->
    <div v-if="invoiceStore.isLoading" class="flex justify-center py-12">
      <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
      </svg>
    </div>

    <!-- Not Found -->
    <div v-else-if="!invoice" class="card p-6 text-center">
      <p class="text-gray-500">Invoice not found</p>
    </div>

    <!-- Invoice Details -->
    <div v-else>
      <!-- Header -->
      <div class="card p-6 mb-6">
        <div class="sm:flex sm:items-center sm:justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ invoice.invoice_number }}</h1>
            <div class="mt-1 flex items-center space-x-2">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="{
                  'bg-yellow-100 text-yellow-800': invoice.status === 'pending',
                  'bg-green-100 text-green-800': invoice.status === 'approved',
                  'bg-red-100 text-red-800': invoice.status === 'rejected',
                  'bg-blue-100 text-blue-800': invoice.status === 'paid',
                }"
              >
                {{ invoice.status_label }}
              </span>
              <span v-if="invoice.is_overdue" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                Overdue
              </span>
            </div>
          </div>
          <div class="mt-4 sm:mt-0 flex space-x-3">
            <NuxtLink v-if="invoice.can_edit && authStore.canWrite" :to="`/invoices/${invoice.id}/edit`" class="btn-secondary">
              Edit
            </NuxtLink>
            <button
              v-if="invoice.can_delete && authStore.canWrite"
              class="btn-danger"
              @click="handleDelete"
            >
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Status Actions -->
      <div v-if="invoice.allowed_transitions.length > 0 && authStore.canApprove" class="card p-4 mb-6">
        <h3 class="text-sm font-medium text-gray-700 mb-3">Update Status</h3>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="status in invoice.allowed_transitions"
            :key="status"
            :disabled="isUpdatingStatus"
            class="btn-secondary text-sm"
            :class="{
              'bg-green-100 text-green-800 hover:bg-green-200': status === 'approved',
              'bg-red-100 text-red-800 hover:bg-red-200': status === 'rejected',
              'bg-blue-100 text-blue-800 hover:bg-blue-200': status === 'paid',
              'bg-yellow-100 text-yellow-800 hover:bg-yellow-200': status === 'pending',
            }"
            @click="handleStatusUpdate(status as InvoiceStatus)"
          >
            {{ InvoiceStatusLabels[status as InvoiceStatus] }}
          </button>
        </div>
      </div>

      <!-- Details Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Invoice Info -->
        <div class="card p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Details</h3>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Invoice Date</dt>
              <dd class="text-sm text-gray-900">{{ invoice.invoice_date || 'N/A' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Due Date</dt>
              <dd class="text-sm text-gray-900">{{ invoice.due_date || 'N/A' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Currency</dt>
              <dd class="text-sm text-gray-900">{{ invoice.currency }}</dd>
            </div>
            <div v-if="invoice.description" class="pt-3 border-t">
              <dt class="text-sm text-gray-500 mb-1">Description</dt>
              <dd class="text-sm text-gray-900">{{ invoice.description }}</dd>
            </div>
            <div v-if="invoice.notes" class="pt-3 border-t">
              <dt class="text-sm text-gray-500 mb-1">Notes</dt>
              <dd class="text-sm text-gray-900">{{ invoice.notes }}</dd>
            </div>
          </dl>
        </div>

        <!-- Vendor Info -->
        <div class="card p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Vendor</h3>
          <div v-if="invoice.vendor">
            <p class="text-sm font-medium text-gray-900">{{ invoice.vendor.name }}</p>
            <p v-if="invoice.vendor.email" class="text-sm text-gray-500">{{ invoice.vendor.email }}</p>
            <p v-if="invoice.vendor.phone" class="text-sm text-gray-500">{{ invoice.vendor.phone }}</p>
            <p v-if="invoice.vendor.full_address" class="text-sm text-gray-500 mt-2">{{ invoice.vendor.full_address }}</p>
          </div>
          <p v-else class="text-sm text-gray-500">No vendor information</p>
        </div>

        <!-- Amounts -->
        <div class="card p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Amounts</h3>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Subtotal</dt>
              <dd class="text-sm text-gray-900">{{ formatCurrency(invoice.subtotal) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Tax</dt>
              <dd class="text-sm text-gray-900">{{ formatCurrency(invoice.tax_amount) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Discount</dt>
              <dd class="text-sm text-gray-900">-{{ formatCurrency(invoice.discount_amount) }}</dd>
            </div>
            <div class="flex justify-between pt-3 border-t">
              <dt class="text-base font-semibold text-gray-900">Total</dt>
              <dd class="text-base font-semibold text-gray-900">{{ formatCurrency(invoice.total_amount) }}</dd>
            </div>
          </dl>
        </div>

        <!-- Payment Info -->
        <div class="card p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Payment</h3>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Paid Date</dt>
              <dd class="text-sm text-gray-900">{{ invoice.paid_date || 'Not paid' }}</dd>
            </div>
            <div v-if="invoice.payment_method" class="flex justify-between">
              <dt class="text-sm text-gray-500">Payment Method</dt>
              <dd class="text-sm text-gray-900">{{ invoice.payment_method }}</dd>
            </div>
            <div v-if="invoice.payment_reference" class="flex justify-between">
              <dt class="text-sm text-gray-500">Reference</dt>
              <dd class="text-sm text-gray-900">{{ invoice.payment_reference }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Audit Info -->
      <div class="card p-6 mt-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Audit Information</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <dt class="text-sm text-gray-500">Created By</dt>
            <dd class="text-sm text-gray-900">{{ invoice.creator?.name || 'N/A' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-500">Created At</dt>
            <dd class="text-sm text-gray-900">{{ invoice.created_at }}</dd>
          </div>
          <div v-if="invoice.approved_by">
            <dt class="text-sm text-gray-500">Approved By</dt>
            <dd class="text-sm text-gray-900">{{ invoice.approver?.name || 'N/A' }}</dd>
          </div>
          <div v-if="invoice.approved_at">
            <dt class="text-sm text-gray-500">Approved At</dt>
            <dd class="text-sm text-gray-900">{{ invoice.approved_at }}</dd>
          </div>
        </dl>
      </div>
    </div>

    <!-- Payment Modal -->
    <Teleport to="body">
      <div v-if="showPaymentModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showPaymentModal = false" />
          <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Mark as Paid</h3>
            <form @submit.prevent="handlePaymentSubmit">
              <div class="space-y-4">
                <div>
                  <label for="payment_method" class="label">Payment Method</label>
                  <input
                    id="payment_method"
                    v-model="paymentForm.payment_method"
                    type="text"
                    class="input"
                    placeholder="e.g., Bank Transfer, Credit Card"
                  />
                </div>
                <div>
                  <label for="payment_reference" class="label">Payment Reference</label>
                  <input
                    id="payment_reference"
                    v-model="paymentForm.payment_reference"
                    type="text"
                    class="input"
                    placeholder="e.g., Transaction ID"
                  />
                </div>
              </div>
              <div class="mt-5 sm:mt-6 flex space-x-3">
                <button type="button" class="btn-secondary flex-1" @click="showPaymentModal = false">
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="isUpdatingStatus"
                  class="btn-primary flex-1"
                  :class="{ 'opacity-50': isUpdatingStatus }"
                >
                  {{ isUpdatingStatus ? 'Saving...' : 'Mark as Paid' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
