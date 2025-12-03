<script setup lang="ts">
import { InvoiceStatus, InvoiceStatusLabels } from '@billing/shared'
import type { InvoiceStatusPayload, ApiErrorResponse } from '@billing/shared'

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

const getStatusVariant = (status: string): 'pending' | 'approved' | 'rejected' | 'paid' => {
  const variants: Record<string, 'pending' | 'approved' | 'rejected' | 'paid'> = {
    pending: 'pending',
    approved: 'approved',
    rejected: 'rejected',
    paid: 'paid',
  }
  return variants[status] || 'pending'
}

const getTransitionButtonClasses = (status: string): string => {
  const classes: Record<string, string> = {
    approved: 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 ring-1 ring-emerald-600/20',
    rejected: 'bg-rose-100 text-rose-700 hover:bg-rose-200 ring-1 ring-rose-600/20',
    paid: 'bg-primary-100 text-primary-700 hover:bg-primary-200 ring-1 ring-primary-600/20',
    pending: 'bg-amber-100 text-amber-700 hover:bg-amber-200 ring-1 ring-amber-600/20',
  }
  return classes[status] || 'bg-gray-100 text-gray-700 hover:bg-gray-200'
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
  <div class="max-w-7xl mx-auto">
    <!-- Loading State -->
    <UiDetailSkeleton v-if="invoiceStore.isLoading" :sections="4" />

    <!-- Not Found State -->
    <div v-else-if="!invoice" class="py-12">
      <UiEmptyState
        title="Invoice not found"
        description="The invoice you're looking for doesn't exist or has been removed."
        action-label="Back to Invoices"
        action-link="/invoices"
      >
        <template #icon>
          <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </template>
      </UiEmptyState>
    </div>

    <!-- Invoice Details -->
    <div v-else>
      <!-- Header -->
      <div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
          <NuxtLink
            to="/invoices"
            class="inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors mb-4"
          >
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Invoices
          </NuxtLink>
          <div class="flex items-center gap-3">
            <h1 class="text-3xl font-bold text-gray-900">{{ invoice.invoice_number }}</h1>
            <UiStatusBadge
              :variant="getStatusVariant(invoice.status)"
              :label="invoice.status_label"
              size="md"
            />
            <UiStatusBadge
              v-if="invoice.is_overdue"
              variant="overdue"
              label="Overdue"
              size="md"
            />
          </div>
          <p class="mt-1 text-sm text-gray-500">
            Created on {{ invoice.created_at }} by {{ invoice.creator?.name || 'Unknown' }}
          </p>
        </div>

        <div class="flex items-center gap-3">
          <NuxtLink
            v-if="invoice.can_edit && authStore.canWrite"
            :to="`/invoices/${invoice.id}/edit`"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
          </NuxtLink>
          <button
            v-if="invoice.can_delete && authStore.canWrite"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-rose-700 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 transition-colors"
            @click="handleDelete"
          >
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <UiStatCard
          label="Subtotal"
          :value="formatCurrency(invoice.subtotal)"
          theme="gray"
        >
          <template #icon>
            <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </template>
        </UiStatCard>

        <UiStatCard
          label="Tax"
          :value="formatCurrency(invoice.tax_amount)"
          theme="amber"
        >
          <template #icon>
            <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
            </svg>
          </template>
        </UiStatCard>

        <UiStatCard
          label="Discount"
          :value="formatCurrency(invoice.discount_amount)"
          theme="rose"
        >
          <template #icon>
            <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
          </template>
        </UiStatCard>

        <UiStatCard
          label="Total"
          :value="formatCurrency(invoice.total_amount)"
          theme="emerald"
        >
          <template #icon>
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </template>
        </UiStatCard>
      </div>

      <!-- Status Actions -->
      <div v-if="invoice.allowed_transitions.length > 0 && authStore.canApprove" class="mb-8">
        <FormSection
          title="Update Status"
          description="Change the invoice status"
          theme="amber"
        >
          <template #icon>
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
          </template>

          <div class="flex flex-wrap gap-3">
            <button
              v-for="status in invoice.allowed_transitions"
              :key="status"
              :disabled="isUpdatingStatus"
              class="inline-flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all disabled:opacity-50"
              :class="getTransitionButtonClasses(status)"
              @click="handleStatusUpdate(status as InvoiceStatus)"
            >
              <svg v-if="status === 'approved'" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <svg v-else-if="status === 'rejected'" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              <svg v-else-if="status === 'paid'" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              {{ InvoiceStatusLabels[status as InvoiceStatus] }}
            </button>
          </div>
        </FormSection>
      </div>

      <!-- Details Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Invoice Details -->
        <FormSection
          title="Invoice Details"
          description="Basic invoice information"
          theme="primary"
        >
          <template #icon>
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </template>

          <dl class="space-y-4">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Invoice Date</dt>
              <dd class="text-sm font-medium text-gray-900">{{ invoice.invoice_date || 'N/A' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Due Date</dt>
              <dd class="text-sm font-medium text-gray-900">{{ invoice.due_date || 'N/A' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Currency</dt>
              <dd class="text-sm font-medium text-gray-900">{{ invoice.currency }}</dd>
            </div>
            <div v-if="invoice.description" class="pt-4 border-t border-gray-200">
              <dt class="text-sm text-gray-500 mb-2">Description</dt>
              <dd class="text-sm text-gray-900 bg-gray-50 rounded-lg p-3">{{ invoice.description }}</dd>
            </div>
            <div v-if="invoice.notes" class="pt-4 border-t border-gray-200">
              <dt class="text-sm text-gray-500 mb-2">Internal Notes</dt>
              <dd class="text-sm text-gray-900 bg-amber-50 rounded-lg p-3 border border-amber-100">{{ invoice.notes }}</dd>
            </div>
          </dl>
        </FormSection>

        <!-- Vendor Info -->
        <FormSection
          title="Vendor Information"
          description="Associated vendor details"
          theme="violet"
        >
          <template #icon>
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </template>

          <div v-if="invoice.vendor">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-12 h-12 rounded-full bg-gradient-to-br from-violet-100 to-violet-200 flex items-center justify-center">
                <span class="text-lg font-bold text-violet-700">{{ invoice.vendor.name.charAt(0).toUpperCase() }}</span>
              </div>
              <div>
                <NuxtLink
                  :to="`/vendors/${invoice.vendor.id}`"
                  class="text-base font-semibold text-gray-900 hover:text-violet-600 transition-colors"
                >
                  {{ invoice.vendor.name }}
                </NuxtLink>
                <p v-if="invoice.vendor.code" class="text-sm text-gray-500">{{ invoice.vendor.code }}</p>
              </div>
            </div>

            <dl class="space-y-3 pt-4 border-t border-gray-200">
              <div v-if="invoice.vendor.email" class="flex items-center text-sm">
                <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span class="text-gray-900">{{ invoice.vendor.email }}</span>
              </div>
              <div v-if="invoice.vendor.phone" class="flex items-center text-sm">
                <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span class="text-gray-900">{{ invoice.vendor.phone }}</span>
              </div>
              <div v-if="invoice.vendor.full_address" class="flex items-start text-sm">
                <svg class="w-4 h-4 text-gray-400 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="text-gray-900">{{ invoice.vendor.full_address }}</span>
              </div>
            </dl>
          </div>
          <p v-else class="text-sm text-gray-500">No vendor information available</p>
        </FormSection>

        <!-- Payment Info -->
        <FormSection
          title="Payment Information"
          description="Payment status and details"
          theme="emerald"
        >
          <template #icon>
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </template>

          <dl class="space-y-4">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Payment Status</dt>
              <dd>
                <UiStatusBadge
                  :variant="invoice.paid_date ? 'paid' : 'pending'"
                  :label="invoice.paid_date ? 'Paid' : 'Unpaid'"
                />
              </dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Paid Date</dt>
              <dd class="text-sm font-medium text-gray-900">{{ invoice.paid_date || 'Not paid yet' }}</dd>
            </div>
            <div v-if="invoice.payment_method" class="flex justify-between">
              <dt class="text-sm text-gray-500">Payment Method</dt>
              <dd class="text-sm font-medium text-gray-900">{{ invoice.payment_method }}</dd>
            </div>
            <div v-if="invoice.payment_reference" class="flex justify-between">
              <dt class="text-sm text-gray-500">Reference</dt>
              <dd class="text-sm font-medium text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">{{ invoice.payment_reference }}</dd>
            </div>
          </dl>
        </FormSection>

        <!-- Audit Information -->
        <FormSection
          title="Audit Trail"
          description="Creation and approval history"
          theme="rose"
        >
          <template #icon>
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </template>

          <div class="space-y-4">
            <!-- Created -->
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">Created</p>
                <p class="text-xs text-gray-500">{{ invoice.created_at }}</p>
                <p class="text-xs text-gray-500">by {{ invoice.creator?.name || 'Unknown' }}</p>
              </div>
            </div>

            <!-- Approved -->
            <div v-if="invoice.approved_at" class="flex items-start gap-3">
              <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">Approved</p>
                <p class="text-xs text-gray-500">{{ invoice.approved_at }}</p>
                <p class="text-xs text-gray-500">by {{ invoice.approver?.name || 'Unknown' }}</p>
              </div>
            </div>

            <!-- Paid -->
            <div v-if="invoice.paid_date" class="flex items-start gap-3">
              <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">Paid</p>
                <p class="text-xs text-gray-500">{{ invoice.paid_date }}</p>
              </div>
            </div>
          </div>
        </FormSection>
      </div>
    </div>

    <!-- Payment Modal -->
    <UiModal
      :show="showPaymentModal"
      title="Mark Invoice as Paid"
      @close="showPaymentModal = false"
    >
      <form @submit.prevent="handlePaymentSubmit">
        <div class="space-y-4">
          <FormInput
            id="payment_method"
            v-model="paymentForm.payment_method"
            label="Payment Method"
            placeholder="e.g., Bank Transfer, Credit Card, Check"
            hint="How was this invoice paid?"
          />
          <FormInput
            id="payment_reference"
            v-model="paymentForm.payment_reference"
            label="Payment Reference"
            placeholder="e.g., Transaction ID, Check Number"
            hint="Reference number for tracking"
          />
        </div>

        <div class="mt-6 flex gap-3">
          <button
            type="button"
            class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            @click="showPaymentModal = false"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="isUpdatingStatus"
            class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors disabled:opacity-50"
          >
            {{ isUpdatingStatus ? 'Processing...' : 'Mark as Paid' }}
          </button>
        </div>
      </form>
    </UiModal>
  </div>
</template>
