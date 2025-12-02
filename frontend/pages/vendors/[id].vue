<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const route = useRoute()
const router = useRouter()
const vendorStore = useVendorStore()
const authStore = useAuthStore()

const vendorId = computed(() => Number(route.params.id))
const vendor = computed(() => vendorStore.currentVendor)

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount)
}

const handleDelete = async () => {
  if (confirm('Are you sure you want to delete this vendor?')) {
    const success = await vendorStore.deleteVendor(vendorId.value)
    if (success) {
      router.push('/vendors')
    }
  }
}

onMounted(async () => {
  await vendorStore.fetchVendor(vendorId.value)
})

onUnmounted(() => {
  vendorStore.clearCurrentVendor()
})
</script>

<template>
  <div>
    <div class="mb-6">
      <NuxtLink to="/vendors" class="text-sm text-gray-500 hover:text-gray-700">
        &larr; Back to Vendors
      </NuxtLink>
    </div>

    <!-- Loading -->
    <div v-if="vendorStore.isLoading" class="flex justify-center py-12">
      <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
      </svg>
    </div>

    <!-- Not Found -->
    <div v-else-if="!vendor" class="card p-6 text-center">
      <p class="text-gray-500">Vendor not found</p>
    </div>

    <!-- Vendor Details -->
    <div v-else>
      <!-- Header -->
      <div class="card p-6 mb-6">
        <div class="sm:flex sm:items-center sm:justify-between">
          <div>
            <div class="flex items-center space-x-3">
              <h1 class="text-2xl font-bold text-gray-900">{{ vendor.name }}</h1>
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="vendor.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
              >
                {{ vendor.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <p v-if="vendor.code" class="mt-1 text-sm text-gray-500">
              Code: {{ vendor.code }}
            </p>
          </div>
          <div v-if="authStore.canWrite" class="mt-4 sm:mt-0 flex space-x-3">
            <NuxtLink :to="`/vendors/${vendor.id}/edit`" class="btn-secondary">
              Edit
            </NuxtLink>
            <button class="btn-danger" @click="handleDelete">
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Details Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Contact Info -->
        <div class="card p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
          <dl class="space-y-3">
            <div v-if="vendor.email" class="flex items-start">
              <dt class="flex-shrink-0">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </dt>
              <dd class="ml-3 text-sm text-gray-900">{{ vendor.email }}</dd>
            </div>
            <div v-if="vendor.phone" class="flex items-start">
              <dt class="flex-shrink-0">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
              </dt>
              <dd class="ml-3 text-sm text-gray-900">{{ vendor.phone }}</dd>
            </div>
            <div v-if="vendor.full_address" class="flex items-start">
              <dt class="flex-shrink-0">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </dt>
              <dd class="ml-3 text-sm text-gray-900">{{ vendor.full_address }}</dd>
            </div>
            <p v-if="!vendor.email && !vendor.phone && !vendor.full_address" class="text-sm text-gray-500">
              No contact information available
            </p>
          </dl>
        </div>

        <!-- Business Info -->
        <div class="card p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Business Information</h3>
          <dl class="space-y-3">
            <div v-if="vendor.tax_id" class="flex justify-between">
              <dt class="text-sm text-gray-500">Tax ID</dt>
              <dd class="text-sm text-gray-900">{{ vendor.tax_id }}</dd>
            </div>
            <div v-if="vendor.payment_terms" class="flex justify-between">
              <dt class="text-sm text-gray-500">Payment Terms</dt>
              <dd class="text-sm text-gray-900">{{ vendor.payment_terms }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Status</dt>
              <dd class="text-sm text-gray-900">{{ vendor.is_active ? 'Active' : 'Inactive' }}</dd>
            </div>
          </dl>
        </div>

        <!-- Invoice Stats -->
        <div class="card p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Statistics</h3>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Total Invoices</dt>
              <dd class="text-sm font-medium text-gray-900">{{ vendor.invoices_count ?? 'N/A' }}</dd>
            </div>
            <div v-if="vendor.pending_invoices_count !== undefined" class="flex justify-between">
              <dt class="text-sm text-gray-500">Pending Invoices</dt>
              <dd class="text-sm font-medium text-gray-900">{{ vendor.pending_invoices_count }}</dd>
            </div>
            <div v-if="vendor.total_invoice_amount !== undefined" class="flex justify-between pt-3 border-t">
              <dt class="text-sm font-medium text-gray-900">Total Amount</dt>
              <dd class="text-sm font-medium text-gray-900">{{ formatCurrency(vendor.total_invoice_amount) }}</dd>
            </div>
          </dl>
          <div class="mt-4 pt-4 border-t">
            <NuxtLink
              :to="{ path: '/invoices', query: { vendor_id: vendor.id } }"
              class="text-sm text-primary-600 hover:text-primary-700"
            >
              View all invoices &rarr;
            </NuxtLink>
          </div>
        </div>

        <!-- Notes -->
        <div class="card p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
          <p v-if="vendor.notes" class="text-sm text-gray-900 whitespace-pre-wrap">{{ vendor.notes }}</p>
          <p v-else class="text-sm text-gray-500">No notes</p>
        </div>
      </div>

      <!-- Timestamps -->
      <div class="card p-6 mt-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <dt class="text-sm text-gray-500">Created</dt>
            <dd class="text-sm text-gray-900">{{ vendor.created_at }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-500">Last Updated</dt>
            <dd class="text-sm text-gray-900">{{ vendor.updated_at }}</dd>
          </div>
        </dl>
      </div>
    </div>
  </div>
</template>
