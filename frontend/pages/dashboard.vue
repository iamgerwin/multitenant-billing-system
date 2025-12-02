<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const authStore = useAuthStore()
const invoiceStore = useInvoiceStore()
const vendorStore = useVendorStore()

const stats = ref({
  totalInvoices: 0,
  pendingInvoices: 0,
  totalVendors: 0,
  totalAmount: 0,
})

const recentInvoices = computed(() => invoiceStore.invoices.slice(0, 5))

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount)
}

onMounted(async () => {
  await Promise.all([
    invoiceStore.fetchInvoices(1, 10),
    vendorStore.fetchVendors(1, 100),
  ])

  stats.value = {
    totalInvoices: invoiceStore.pagination.total,
    pendingInvoices: invoiceStore.pendingInvoices.length,
    totalVendors: vendorStore.pagination.total,
    totalAmount: invoiceStore.totalAmount,
  }
})
</script>

<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
      <p class="mt-1 text-sm text-gray-500">
        Welcome back, {{ authStore.currentUser?.name }}
      </p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
      <div class="card p-5">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Total Invoices</dt>
              <dd class="text-lg font-semibold text-gray-900">{{ stats.totalInvoices }}</dd>
            </dl>
          </div>
        </div>
      </div>

      <div class="card p-5">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Pending Invoices</dt>
              <dd class="text-lg font-semibold text-gray-900">{{ stats.pendingInvoices }}</dd>
            </dl>
          </div>
        </div>
      </div>

      <div class="card p-5">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Total Vendors</dt>
              <dd class="text-lg font-semibold text-gray-900">{{ stats.totalVendors }}</dd>
            </dl>
          </div>
        </div>
      </div>

      <div class="card p-5">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Total Amount</dt>
              <dd class="text-lg font-semibold text-gray-900">{{ formatCurrency(stats.totalAmount) }}</dd>
            </dl>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Invoices -->
    <div class="card">
      <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-900">Recent Invoices</h3>
          <NuxtLink to="/invoices" class="text-sm text-primary-600 hover:text-primary-700">
            View all
          </NuxtLink>
        </div>
      </div>
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
                Date
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="invoice in recentInvoices" :key="invoice.id">
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
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ invoice.invoice_date }}
              </td>
            </tr>
            <tr v-if="recentInvoices.length === 0">
              <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                No invoices found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
