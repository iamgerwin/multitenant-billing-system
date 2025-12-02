<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const vendorStore = useVendorStore()
const authStore = useAuthStore()

const currentPage = ref(1)

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount)
}

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

onMounted(() => {
  loadVendors()
})
</script>

<template>
  <div>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Vendors</h1>
        <p class="mt-1 text-sm text-gray-500">
          Manage your vendor directory
        </p>
      </div>
      <div class="mt-4 sm:mt-0">
        <NuxtLink v-if="authStore.canWrite" to="/vendors/create" class="btn-primary">
          Add Vendor
        </NuxtLink>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="vendorStore.isLoading" class="flex justify-center py-12">
      <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
      </svg>
    </div>

    <!-- Vendors Grid -->
    <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="vendor in vendorStore.vendors" :key="vendor.id" class="card p-6">
        <div class="flex items-start justify-between">
          <div class="flex-1 min-w-0">
            <NuxtLink :to="`/vendors/${vendor.id}`" class="text-lg font-medium text-gray-900 hover:text-primary-600 truncate block">
              {{ vendor.name }}
            </NuxtLink>
            <p v-if="vendor.code" class="text-sm text-gray-500">
              Code: {{ vendor.code }}
            </p>
          </div>
          <span
            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
            :class="vendor.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
          >
            {{ vendor.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>

        <div class="mt-4 space-y-2">
          <p v-if="vendor.email" class="text-sm text-gray-600 flex items-center">
            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            {{ vendor.email }}
          </p>
          <p v-if="vendor.phone" class="text-sm text-gray-600 flex items-center">
            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            {{ vendor.phone }}
          </p>
        </div>

        <div v-if="vendor.invoices_count !== undefined" class="mt-4 pt-4 border-t border-gray-200">
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Invoices</span>
            <span class="font-medium text-gray-900">{{ vendor.invoices_count }}</span>
          </div>
          <div v-if="vendor.total_invoice_amount !== undefined" class="flex justify-between text-sm mt-1">
            <span class="text-gray-500">Total Amount</span>
            <span class="font-medium text-gray-900">{{ formatCurrency(vendor.total_invoice_amount) }}</span>
          </div>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-200 flex justify-end space-x-3">
          <NuxtLink :to="`/vendors/${vendor.id}`" class="text-sm text-primary-600 hover:text-primary-700">
            View
          </NuxtLink>
          <NuxtLink v-if="authStore.canWrite" :to="`/vendors/${vendor.id}/edit`" class="text-sm text-primary-600 hover:text-primary-700">
            Edit
          </NuxtLink>
          <button
            v-if="authStore.canWrite"
            class="text-sm text-red-600 hover:text-red-700"
            @click="handleDelete(vendor.id)"
          >
            Delete
          </button>
        </div>
      </div>

      <div v-if="vendorStore.vendors.length === 0" class="col-span-full card p-6 text-center">
        <p class="text-gray-500">No vendors found</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="vendorStore.pagination.lastPage > 1" class="mt-6 flex justify-center">
      <div class="flex space-x-2">
        <button
          :disabled="currentPage === 1"
          class="btn-secondary"
          :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }"
          @click="handlePageChange(currentPage - 1)"
        >
          Previous
        </button>
        <span class="px-4 py-2 text-sm text-gray-700">
          Page {{ currentPage }} of {{ vendorStore.pagination.lastPage }}
        </span>
        <button
          :disabled="currentPage === vendorStore.pagination.lastPage"
          class="btn-secondary"
          :class="{ 'opacity-50 cursor-not-allowed': currentPage === vendorStore.pagination.lastPage }"
          @click="handlePageChange(currentPage + 1)"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>
