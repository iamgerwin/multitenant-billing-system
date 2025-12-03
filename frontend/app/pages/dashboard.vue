<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const authStore = useAuthStore()
const invoiceStore = useInvoiceStore()
const vendorStore = useVendorStore()

const isLoading = ref(true)

const stats = ref({
  totalInvoices: 0,
  pendingInvoices: 0,
  approvedInvoices: 0,
  totalVendors: 0,
  totalAmount: 0,
  paidAmount: 0,
})

const recentInvoices = computed(() => invoiceStore.invoices.slice(0, 5))

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

onMounted(async () => {
  await Promise.all([
    invoiceStore.fetchInvoices(1, 10),
    vendorStore.fetchVendors(1, 100),
  ])

  const paidInvoices = invoiceStore.invoices.filter((i) => i.status === 'paid')
  const paidAmount = paidInvoices.reduce((sum, i) => sum + i.total_amount, 0)

  stats.value = {
    totalInvoices: invoiceStore.pagination.total,
    pendingInvoices: invoiceStore.pendingInvoices.length,
    approvedInvoices: invoiceStore.invoices.filter((i) => i.status === 'approved').length,
    totalVendors: vendorStore.pagination.total,
    totalAmount: invoiceStore.totalAmount,
    paidAmount,
  }

  isLoading.value = false
})
</script>

<template>
  <div class="max-w-7xl mx-auto">
    <UiHeader
      title="Dashboard"
      :description="`Welcome back, ${authStore.currentUser?.name || 'User'}`"
    />

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Total Invoices -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 bg-gradient-to-r from-primary-50 to-primary-100 border-b border-primary-200">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-primary-600 flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Invoices</p>
            </div>
          </div>
        </div>
        <div class="px-5 py-4">
          <div v-if="isLoading" class="h-8 w-16 bg-gray-200 rounded animate-pulse" />
          <p v-else class="text-3xl font-bold text-gray-900">{{ stats.totalInvoices }}</p>
          <p class="text-xs text-gray-500 mt-1">All time</p>
        </div>
      </div>

      <!-- Pending Invoices -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 bg-gradient-to-r from-amber-50 to-amber-100 border-b border-amber-200">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-amber-600 flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Pending Approval</p>
            </div>
          </div>
        </div>
        <div class="px-5 py-4">
          <div v-if="isLoading" class="h-8 w-16 bg-gray-200 rounded animate-pulse" />
          <p v-else class="text-3xl font-bold text-gray-900">{{ stats.pendingInvoices }}</p>
          <p class="text-xs text-gray-500 mt-1">Awaiting review</p>
        </div>
      </div>

      <!-- Total Vendors -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 bg-gradient-to-r from-violet-50 to-violet-100 border-b border-violet-200">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-violet-600 flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Vendors</p>
            </div>
          </div>
        </div>
        <div class="px-5 py-4">
          <div v-if="isLoading" class="h-8 w-16 bg-gray-200 rounded animate-pulse" />
          <p v-else class="text-3xl font-bold text-gray-900">{{ stats.totalVendors }}</p>
          <p class="text-xs text-gray-500 mt-1">Active partners</p>
        </div>
      </div>

      <!-- Total Amount -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 bg-gradient-to-r from-emerald-50 to-emerald-100 border-b border-emerald-200">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Amount</p>
            </div>
          </div>
        </div>
        <div class="px-5 py-4">
          <div v-if="isLoading" class="h-8 w-24 bg-gray-200 rounded animate-pulse" />
          <p v-else class="text-3xl font-bold text-gray-900">{{ formatCurrency(stats.totalAmount) }}</p>
          <p class="text-xs text-gray-500 mt-1">All invoices</p>
        </div>
      </div>
    </div>

    <!-- Quick Actions & Recent Invoices -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Quick Actions -->
      <div class="lg:col-span-1">
        <FormSection
          title="Quick Actions"
          description="Common tasks"
          theme="rose"
        >
          <template #icon>
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </template>

          <div class="space-y-3">
            <NuxtLink
              to="/invoices/create"
              class="flex items-center p-4 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-primary-50 transition-all group"
            >
              <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center group-hover:bg-primary-200 transition-colors">
                <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-900 group-hover:text-primary-700">Create Invoice</p>
                <p class="text-xs text-gray-500">Add a new invoice</p>
              </div>
              <svg class="w-5 h-5 text-gray-400 ml-auto group-hover:text-primary-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </NuxtLink>

            <NuxtLink
              to="/vendors/create"
              class="flex items-center p-4 rounded-lg border border-gray-200 hover:border-violet-300 hover:bg-violet-50 transition-all group"
            >
              <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center group-hover:bg-violet-200 transition-colors">
                <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-900 group-hover:text-violet-700">Add Vendor</p>
                <p class="text-xs text-gray-500">Register a new vendor</p>
              </div>
              <svg class="w-5 h-5 text-gray-400 ml-auto group-hover:text-violet-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </NuxtLink>

            <NuxtLink
              to="/invoices"
              class="flex items-center p-4 rounded-lg border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50 transition-all group"
            >
              <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-900 group-hover:text-emerald-700">View All Invoices</p>
                <p class="text-xs text-gray-500">Manage your invoices</p>
              </div>
              <svg class="w-5 h-5 text-gray-400 ml-auto group-hover:text-emerald-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </NuxtLink>

            <NuxtLink
              to="/vendors"
              class="flex items-center p-4 rounded-lg border border-gray-200 hover:border-amber-300 hover:bg-amber-50 transition-all group"
            >
              <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center group-hover:bg-amber-200 transition-colors">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-900 group-hover:text-amber-700">View All Vendors</p>
                <p class="text-xs text-gray-500">Manage your vendors</p>
              </div>
              <svg class="w-5 h-5 text-gray-400 ml-auto group-hover:text-amber-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </NuxtLink>
          </div>
        </FormSection>
      </div>

      <!-- Recent Invoices -->
      <div class="lg:col-span-2">
        <FormSection
          title="Recent Invoices"
          description="Latest invoice activity"
          theme="primary"
        >
          <template #icon>
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </template>

          <!-- Loading State -->
          <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 3" :key="i" class="flex items-center p-4 rounded-lg border border-gray-100 bg-gray-50">
              <div class="flex-1 space-y-2">
                <div class="h-4 w-24 bg-gray-200 rounded animate-pulse" />
                <div class="h-3 w-32 bg-gray-200 rounded animate-pulse" />
              </div>
              <div class="h-6 w-16 bg-gray-200 rounded-full animate-pulse" />
            </div>
          </div>

          <!-- Empty State -->
          <div v-else-if="recentInvoices.length === 0" class="text-center py-12">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
              <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 class="text-sm font-medium text-gray-900 mb-1">No invoices yet</h3>
            <p class="text-sm text-gray-500 mb-4">Get started by creating your first invoice.</p>
            <NuxtLink
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
              v-for="invoice in recentInvoices"
              :key="invoice.id"
              :to="`/invoices/${invoice.id}`"
              class="flex items-center p-4 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-primary-50/50 transition-all group"
            >
              <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center group-hover:bg-primary-100 transition-colors">
                <svg class="w-5 h-5 text-gray-500 group-hover:text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div class="ml-4 flex-1 min-w-0">
                <div class="flex items-center justify-between">
                  <p class="text-sm font-medium text-gray-900 group-hover:text-primary-700 truncate">
                    {{ invoice.invoice_number }}
                  </p>
                  <span
                    class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getStatusClasses(invoice.status)"
                  >
                    {{ invoice.status_label }}
                  </span>
                </div>
                <div class="flex items-center justify-between mt-1">
                  <p class="text-sm text-gray-500 truncate">{{ invoice.vendor?.name || 'N/A' }}</p>
                  <p class="text-sm font-medium text-gray-900">{{ formatCurrency(invoice.total_amount) }}</p>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ invoice.invoice_date }}</p>
              </div>
              <svg class="w-5 h-5 text-gray-400 ml-4 group-hover:text-primary-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </NuxtLink>

            <!-- View All Link -->
            <div class="pt-4 border-t border-gray-200">
              <NuxtLink
                to="/invoices"
                class="flex items-center justify-center text-sm font-medium text-primary-600 hover:text-primary-700 transition-colors"
              >
                View all invoices
                <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
              </NuxtLink>
            </div>
          </div>
        </FormSection>
      </div>
    </div>
  </div>
</template>
