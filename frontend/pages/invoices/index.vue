<script setup lang="ts">
import { InvoiceStatus, InvoiceStatusLabels } from '~/types'

definePageMeta({
  middleware: 'auth',
})

const invoiceStore = useInvoiceStore()
const authStore = useAuthStore()

const currentPage = ref(1)
const selectedStatus = ref<InvoiceStatus | ''>('')

const statusOptions = [
  { value: '', label: 'All Statuses' },
  ...Object.entries(InvoiceStatusLabels).map(([value, label]) => ({
    value: value as InvoiceStatus,
    label,
  })),
]

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount)
}

const loadInvoices = async () => {
  if (selectedStatus.value) {
    invoiceStore.setFilters({ status: selectedStatus.value })
  } else {
    invoiceStore.clearFilters()
  }
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

const handleDelete = async (id: number) => {
  if (confirm('Are you sure you want to delete this invoice?')) {
    await invoiceStore.deleteInvoice(id)
  }
}

onMounted(() => {
  loadInvoices()
})
</script>

<template>
  <div>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
        <p class="mt-1 text-sm text-gray-500">
          Manage your invoices
        </p>
      </div>
      <div class="mt-4 sm:mt-0">
        <NuxtLink v-if="authStore.canWrite" to="/invoices/create" class="btn-primary">
          Create Invoice
        </NuxtLink>
      </div>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-6">
      <div class="flex flex-wrap gap-4 items-center">
        <div>
          <label for="status" class="label">Status</label>
          <select
            id="status"
            v-model="selectedStatus"
            class="input w-40"
            @change="handleStatusFilter"
          >
            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="invoiceStore.isLoading" class="flex justify-center py-12">
      <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
      </svg>
    </div>

    <!-- Table -->
    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Invoice #
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Vendor
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Amount
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Due Date
              </th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="invoice in invoiceStore.invoices" :key="invoice.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <NuxtLink :to="`/invoices/${invoice.id}`" class="text-primary-600 hover:text-primary-700 font-medium">
                  {{ invoice.invoice_number }}
                </NuxtLink>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ invoice.vendor?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(invoice.total_amount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
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
                <span v-if="invoice.is_overdue" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                  Overdue
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ invoice.due_date || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <NuxtLink :to="`/invoices/${invoice.id}`" class="text-primary-600 hover:text-primary-700 mr-3">
                  View
                </NuxtLink>
                <NuxtLink v-if="invoice.can_edit && authStore.canWrite" :to="`/invoices/${invoice.id}/edit`" class="text-primary-600 hover:text-primary-700 mr-3">
                  Edit
                </NuxtLink>
                <button
                  v-if="invoice.can_delete && authStore.canWrite"
                  class="text-red-600 hover:text-red-700"
                  @click="handleDelete(invoice.id)"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="invoiceStore.invoices.length === 0">
              <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                No invoices found
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="invoiceStore.pagination.lastPage > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
          <button
            :disabled="currentPage === 1"
            class="btn-secondary"
            @click="handlePageChange(currentPage - 1)"
          >
            Previous
          </button>
          <button
            :disabled="currentPage === invoiceStore.pagination.lastPage"
            class="btn-secondary"
            @click="handlePageChange(currentPage + 1)"
          >
            Next
          </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing page <span class="font-medium">{{ currentPage }}</span> of
              <span class="font-medium">{{ invoiceStore.pagination.lastPage }}</span>
              (<span class="font-medium">{{ invoiceStore.pagination.total }}</span> total)
            </p>
          </div>
          <div class="flex space-x-2">
            <button
              :disabled="currentPage === 1"
              class="btn-secondary"
              :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }"
              @click="handlePageChange(currentPage - 1)"
            >
              Previous
            </button>
            <button
              :disabled="currentPage === invoiceStore.pagination.lastPage"
              class="btn-secondary"
              :class="{ 'opacity-50 cursor-not-allowed': currentPage === invoiceStore.pagination.lastPage }"
              @click="handlePageChange(currentPage + 1)"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
