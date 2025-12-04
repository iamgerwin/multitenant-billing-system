<script setup lang="ts">
import { InvoiceStatus, InvoiceStatusLabels } from '@billing/shared'

definePageMeta({
  middleware: 'auth',
})

const route = useRoute()
const invoiceStore = useInvoiceStore()
const vendorStore = useVendorStore()
const authStore = useAuthStore()

const currentPage = ref(1)
const selectedStatus = ref<InvoiceStatus | ''>('')
const selectedSort = ref('-created_at')

// Get vendor_id from route query
const vendorId = computed(() => {
  const id = route.query.vendor_id
  return id ? Number(id) : null
})

// Fetch vendor info when vendor_id is present
const vendor = computed(() => vendorStore.currentVendor)

// Page title based on vendor filter
const pageTitle = computed(() => {
  if (vendor.value && vendorId.value) {
    return `Invoices for ${vendor.value.name}`
  }
  return 'Invoices'
})

// Page description based on vendor filter
const pageDescription = computed(() => {
  if (vendor.value && vendorId.value) {
    return `Manage and track invoices for ${vendor.value.name}`
  }
  return 'Manage and track all your invoices'
})

// Set page head title dynamically
useHead({
  title: computed(() => pageTitle.value),
})

const statusOptions = [
  { value: '', label: 'All Statuses' },
  ...Object.entries(InvoiceStatusLabels).map(([value, label]) => ({
    value: value as InvoiceStatus,
    label,
  })),
]

const sortOptions = [
  { value: '-created_at', label: 'Newest First' },
  { value: 'created_at', label: 'Oldest First' },
  { value: '-total_amount', label: 'Highest Amount' },
  { value: 'total_amount', label: 'Lowest Amount' },
  { value: '-due_date', label: 'Due Date (Latest)' },
  { value: 'due_date', label: 'Due Date (Earliest)' },
  { value: 'invoice_number', label: 'Invoice # (A-Z)' },
  { value: '-invoice_number', label: 'Invoice # (Z-A)' },
]

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount)
}

const getStatusClasses = (status: string): string => {
  const classes: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-800 ring-1 ring-amber-600/20',
    approved: 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-600/20',
    rejected: 'bg-rose-100 text-rose-800 ring-1 ring-rose-600/20',
    paid: 'bg-primary-100 text-primary-800 ring-1 ring-primary-600/20',
  }
  return classes[status] || 'bg-gray-100 text-gray-800 ring-1 ring-gray-600/20'
}

const stats = computed(() => ({
  total: invoiceStore.pagination.total,
  pending: invoiceStore.invoices.filter((i) => i.status === 'pending').length,
  approved: invoiceStore.invoices.filter((i) => i.status === 'approved').length,
  totalAmount: invoiceStore.totalAmount,
}))

const loadInvoices = async () => {
  const filters: { status?: InvoiceStatus; sort?: string; vendor_id?: number } = {
    sort: selectedSort.value,
  }
  if (selectedStatus.value) {
    filters.status = selectedStatus.value
  }
  if (vendorId.value) {
    filters.vendor_id = vendorId.value
  }
  invoiceStore.setFilters(filters)
  await invoiceStore.fetchInvoices(currentPage.value)
}

const handlePageChange = (page: number) => {
  currentPage.value = page
  loadInvoices()
}

const handleStatusFilter = () => {
  currentPage.value = 1
  loadInvoices()
}

const handleSortChange = () => {
  currentPage.value = 1
  loadInvoices()
}

const handleDelete = async (id: number) => {
  if (confirm('Are you sure you want to delete this invoice?')) {
    await invoiceStore.deleteInvoice(id)
  }
}

onMounted(async () => {
  // Fetch vendor info if vendor_id is in query params
  if (vendorId.value) {
    await vendorStore.fetchVendor(vendorId.value)
  }
  await loadInvoices()
})

onUnmounted(() => {
  // Clear vendor if we were filtering by vendor_id
  if (vendorId.value) {
    vendorStore.clearCurrentVendor()
  }
})
</script>

<template>
  <div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <UiHeader
          :title="pageTitle"
          :description="pageDescription"
          :back-link="vendorId ? `/vendors/${vendorId}` : undefined"
          :back-label="vendorId ? 'Back to Vendor' : undefined"
        />
        <!-- Vendor filter indicator -->
        <div v-if="vendor && vendorId" class="mt-2 flex items-center gap-2">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-violet-100 text-violet-800">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            Filtered by: {{ vendor.name }}
          </span>
          <NuxtLink
            to="/invoices"
            class="inline-flex items-center px-2 py-1 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded transition-colors"
          >
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Clear filter
          </NuxtLink>
        </div>
      </div>
      <NuxtLink
        v-if="authStore.canWrite"
        to="/invoices/create"
        class="inline-flex items-center px-4 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors shadow-sm"
      >
        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Create Invoice
      </NuxtLink>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center">
          <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total</p>
            <p class="text-xl font-bold text-gray-900">{{ stats.total }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center">
          <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pending</p>
            <p class="text-xl font-bold text-gray-900">{{ stats.pending }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center">
          <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Approved</p>
            <p class="text-xl font-bold text-gray-900">{{ stats.approved }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center">
          <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Value</p>
            <p class="text-xl font-bold text-gray-900">{{ formatCurrency(stats.totalAmount) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters & Table Section -->
    <FormSection
      title="Invoice List"
      description="View and manage all invoices"
      theme="primary"
    >
      <template #icon>
        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
      </template>

      <!-- Filter Bar -->
      <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <div class="flex flex-wrap items-center gap-4">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Filter by:</span>
          </div>
          <div class="flex-1 max-w-xs">
            <select
              id="status"
              v-model="selectedStatus"
              class="block w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
              @change="handleStatusFilter"
            >
              <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Sort by:</span>
          </div>
          <div class="flex-1 max-w-xs">
            <select
              id="sort"
              v-model="selectedSort"
              class="block w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
              @change="handleSortChange"
            >
              <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>
          <button
            v-if="selectedStatus"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1"
            @click="selectedStatus = ''; handleStatusFilter()"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Clear filter
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="invoiceStore.isLoading" class="space-y-4">
        <div v-for="i in 5" :key="i" class="flex items-center p-4 rounded-lg border border-gray-100 bg-gray-50">
          <div class="flex-shrink-0 w-10 h-10 bg-gray-200 rounded-lg animate-pulse" />
          <div class="ml-4 flex-1 space-y-2">
            <div class="h-4 w-32 bg-gray-200 rounded animate-pulse" />
            <div class="h-3 w-24 bg-gray-200 rounded animate-pulse" />
          </div>
          <div class="h-6 w-20 bg-gray-200 rounded-full animate-pulse" />
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="invoiceStore.invoices.length === 0" class="text-center py-12">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
          <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <h3 class="text-sm font-medium text-gray-900 mb-1">No invoices found</h3>
        <p class="text-sm text-gray-500 mb-4">
          {{ selectedStatus ? 'Try changing the filter or create a new invoice.' : 'Get started by creating your first invoice.' }}
        </p>
        <NuxtLink
          v-if="authStore.canWrite"
          to="/invoices/create"
          class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors"
        >
          <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Invoice
        </NuxtLink>
      </div>

      <!-- Invoice List -->
      <div v-else class="space-y-3">
        <NuxtLink
          v-for="invoice in invoiceStore.invoices"
          :key="invoice.id"
          :to="`/invoices/${invoice.id}`"
          class="flex items-center p-4 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-primary-50/50 transition-all group"
        >
          <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center group-hover:bg-primary-100 transition-colors">
            <svg class="w-6 h-6 text-gray-500 group-hover:text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>

          <div class="ml-4 flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <p class="text-sm font-semibold text-gray-900 group-hover:text-primary-700">
                {{ invoice.invoice_number }}
              </p>
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="getStatusClasses(invoice.status)"
              >
                {{ invoice.status_label }}
              </span>
              <span
                v-if="invoice.is_overdue"
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-800 ring-1 ring-rose-600/20"
              >
                Overdue
              </span>
            </div>
            <div class="flex items-center gap-4 mt-1">
              <p class="text-sm text-gray-500 truncate">{{ invoice.vendor?.name || 'N/A' }}</p>
              <span class="text-gray-300">|</span>
              <p class="text-sm text-gray-500">Due: {{ invoice.due_date || 'N/A' }}</p>
            </div>
          </div>

          <div class="ml-4 text-right">
            <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(invoice.total_amount) }}</p>
            <p class="text-xs text-gray-500">{{ invoice.invoice_date }}</p>
          </div>

          <div class="ml-4 flex items-center gap-2">
            <button
              v-if="invoice.can_edit && authStore.canWrite"
              class="p-2 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
              title="Edit"
              @click.prevent="$router.push(`/invoices/${invoice.id}/edit`)"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button
              v-if="invoice.can_delete && authStore.canWrite"
              class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
              title="Delete"
              @click.prevent="handleDelete(invoice.id)"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
            <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
        </NuxtLink>
      </div>

      <!-- Pagination -->
      <div v-if="invoiceStore.pagination.lastPage > 1" class="mt-6 pt-6 border-t border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <p class="text-sm text-gray-700">
            Showing page <span class="font-medium">{{ currentPage }}</span> of
            <span class="font-medium">{{ invoiceStore.pagination.lastPage }}</span>
            (<span class="font-medium">{{ invoiceStore.pagination.total }}</span> total invoices)
          </p>
          <div class="flex items-center gap-2">
            <button
              :disabled="currentPage === 1"
              class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg border transition-colors"
              :class="currentPage === 1
                ? 'border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed'
                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
              @click="handlePageChange(currentPage - 1)"
            >
              <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Previous
            </button>
            <button
              :disabled="currentPage === invoiceStore.pagination.lastPage"
              class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg border transition-colors"
              :class="currentPage === invoiceStore.pagination.lastPage
                ? 'border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed'
                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
              @click="handlePageChange(currentPage + 1)"
            >
              Next
              <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </FormSection>
  </div>
</template>
