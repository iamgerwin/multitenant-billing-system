<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

useHead({
  title: 'Vendors',
})

const vendorStore = useVendorStore()
const authStore = useAuthStore()
const statsStore = useStatsStore()

const currentPage = ref(1)
const searchQuery = ref('')

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount)
}

const stats = computed(() => ({
  total: statsStore.totalVendorCount,
  active: statsStore.activeVendorCount,
  inactive: statsStore.totalVendorCount - statsStore.activeVendorCount,
  totalAmount: statsStore.totalInvoiceAmount,
}))

const filteredVendors = computed(() => {
  if (!searchQuery.value) return vendorStore.vendors
  const query = searchQuery.value.toLowerCase()
  return vendorStore.vendors.filter(
    (v) =>
      v.name.toLowerCase().includes(query) ||
      v.code?.toLowerCase().includes(query) ||
      v.email?.toLowerCase().includes(query)
  )
})

const loadVendors = async () => {
  await vendorStore.fetchVendors(currentPage.value)
}

const handlePageChange = (page: number) => {
  currentPage.value = page
  loadVendors()
}

const handleDelete = async (id: number) => {
  if (confirm('Are you sure you want to delete this vendor?')) {
    await vendorStore.deleteVendor(id)
  }
}

onMounted(async () => {
  await Promise.all([
    statsStore.fetchStats(),
    loadVendors(),
  ])
})
</script>

<template>
  <div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <UiHeader
        title="Vendors"
        description="Manage your vendor directory"
      />
      <NuxtLink
        v-if="authStore.canWrite"
        to="/vendors/create"
        class="inline-flex items-center px-4 py-2.5 bg-violet-600 text-white text-sm font-medium rounded-lg hover:bg-violet-700 transition-colors shadow-sm"
      >
        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
        </svg>
        Add Vendor
      </NuxtLink>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center">
          <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
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
          <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Active</p>
            <p class="text-xl font-bold text-gray-900">{{ stats.active }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center">
          <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Inactive</p>
            <p class="text-xl font-bold text-gray-900">{{ stats.inactive }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center">
          <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

    <!-- Vendor List Section -->
    <FormSection
      title="Vendor Directory"
      description="All registered vendors"
      theme="violet"
    >
      <template #icon>
        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
      </template>

      <!-- Search Bar -->
      <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <div class="flex flex-wrap items-center gap-4">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Search:</span>
          </div>
          <div class="flex-1 max-w-md">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by name, code, or email..."
              class="block w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
            />
          </div>
          <button
            v-if="searchQuery"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1"
            @click="searchQuery = ''"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Clear search
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="vendorStore.isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="i in 6" :key="i" class="p-5 rounded-lg border border-gray-100 bg-gray-50">
          <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-gray-200 rounded-full animate-pulse" />
            <div class="flex-1 space-y-2">
              <div class="h-5 w-32 bg-gray-200 rounded animate-pulse" />
              <div class="h-4 w-24 bg-gray-200 rounded animate-pulse" />
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-gray-200 space-y-2">
            <div class="h-3 w-full bg-gray-200 rounded animate-pulse" />
            <div class="h-3 w-3/4 bg-gray-200 rounded animate-pulse" />
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredVendors.length === 0" class="text-center py-12">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
          <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <h3 class="text-sm font-medium text-gray-900 mb-1">No vendors found</h3>
        <p class="text-sm text-gray-500 mb-4">
          {{ searchQuery ? 'Try changing the search query or add a new vendor.' : 'Get started by adding your first vendor.' }}
        </p>
        <NuxtLink
          v-if="authStore.canWrite"
          to="/vendors/create"
          class="inline-flex items-center px-4 py-2 bg-violet-600 text-white text-sm font-medium rounded-lg hover:bg-violet-700 transition-colors"
        >
          <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
          </svg>
          Add Vendor
        </NuxtLink>
      </div>

      <!-- Vendor Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <NuxtLink
          v-for="vendor in filteredVendors"
          :key="vendor.id"
          :to="`/vendors/${vendor.id}`"
          class="block p-5 rounded-lg border border-gray-200 hover:border-violet-300 hover:bg-violet-50/50 transition-all group"
        >
          <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
              <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-br from-violet-100 to-violet-200 flex items-center justify-center group-hover:from-violet-200 group-hover:to-violet-300 transition-colors">
                <span class="text-lg font-bold text-violet-700">
                  {{ vendor.name.charAt(0).toUpperCase() }}
                </span>
              </div>
              <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 group-hover:text-violet-700 truncate">
                  {{ vendor.name }}
                </p>
                <p v-if="vendor.code" class="text-xs text-gray-500">
                  {{ vendor.code }}
                </p>
              </div>
            </div>
            <span
              class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
              :class="vendor.is_active
                ? 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-600/20'
                : 'bg-gray-100 text-gray-800 ring-1 ring-gray-600/20'"
            >
              {{ vendor.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>

          <div class="mt-4 space-y-2">
            <p v-if="vendor.email" class="text-sm text-gray-600 flex items-center truncate">
              <svg class="flex-shrink-0 h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              {{ vendor.email }}
            </p>
            <p v-if="vendor.phone" class="text-sm text-gray-600 flex items-center">
              <svg class="flex-shrink-0 h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              {{ vendor.phone }}
            </p>
          </div>

          <div v-if="vendor.invoices_count !== undefined" class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex justify-between items-center">
              <div class="flex items-center gap-4 text-sm">
                <span class="text-gray-500">
                  <span class="font-medium text-gray-900">{{ vendor.invoices_count }}</span> invoices
                </span>
                <span v-if="vendor.total_invoice_amount !== undefined" class="text-gray-500">
                  <span class="font-medium text-gray-900">{{ formatCurrency(vendor.total_invoice_amount) }}</span>
                </span>
              </div>
              <svg class="w-5 h-5 text-gray-400 group-hover:text-violet-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </div>

          <div class="mt-4 pt-4 border-t border-gray-200 flex justify-end gap-2" @click.prevent>
            <button
              v-if="authStore.canWrite"
              class="p-2 text-gray-400 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition-colors"
              title="Edit"
              @click.prevent="$router.push(`/vendors/${vendor.id}/edit`)"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button
              v-if="authStore.canWrite"
              class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
              title="Delete"
              @click.prevent="handleDelete(vendor.id)"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </NuxtLink>
      </div>

      <!-- Pagination -->
      <div v-if="vendorStore.pagination.lastPage > 1" class="mt-6 pt-6 border-t border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <p class="text-sm text-gray-700">
            Showing page <span class="font-medium">{{ currentPage }}</span> of
            <span class="font-medium">{{ vendorStore.pagination.lastPage }}</span>
            (<span class="font-medium">{{ vendorStore.pagination.total }}</span> total vendors)
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
              :disabled="currentPage === vendorStore.pagination.lastPage"
              class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg border transition-colors"
              :class="currentPage === vendorStore.pagination.lastPage
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
