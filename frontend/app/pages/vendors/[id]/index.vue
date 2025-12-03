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
  <div class="max-w-7xl mx-auto">
    <!-- Loading State -->
    <UiDetailSkeleton v-if="vendorStore.isLoading" :sections="4" />

    <!-- Not Found State -->
    <div v-else-if="!vendor" class="py-12">
      <UiEmptyState
        title="Vendor not found"
        description="The vendor you're looking for doesn't exist or has been removed."
        action-label="Back to Vendors"
        action-link="/vendors"
      >
        <template #icon>
          <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </template>
      </UiEmptyState>
    </div>

    <!-- Vendor Details -->
    <div v-else>
      <!-- Header -->
      <div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
          <NuxtLink
            to="/vendors"
            class="inline-flex items-center text-sm text-gray-500 hover:text-violet-600 transition-colors mb-4"
          >
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Vendors
          </NuxtLink>
          <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-violet-100 to-violet-200 flex items-center justify-center">
              <span class="text-2xl font-bold text-violet-700">{{ vendor.name.charAt(0).toUpperCase() }}</span>
            </div>
            <div>
              <div class="flex items-center gap-3">
                <h1 class="text-3xl font-bold text-gray-900">{{ vendor.name }}</h1>
                <UiStatusBadge
                  :variant="vendor.is_active ? 'active' : 'inactive'"
                  :label="vendor.is_active ? 'Active' : 'Inactive'"
                  size="md"
                />
              </div>
              <p v-if="vendor.code" class="mt-1 text-sm text-gray-500">
                Code: {{ vendor.code }}
              </p>
            </div>
          </div>
        </div>

        <div v-if="authStore.canWrite" class="flex items-center gap-3">
          <NuxtLink
            :to="`/vendors/${vendor.id}/edit`"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
          </NuxtLink>
          <button
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
          label="Total Invoices"
          :value="vendor.invoices_count ?? 0"
          theme="violet"
        >
          <template #icon>
            <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </template>
        </UiStatCard>

        <UiStatCard
          label="Pending"
          :value="vendor.pending_invoices_count ?? 0"
          theme="amber"
        >
          <template #icon>
            <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </template>
        </UiStatCard>

        <UiStatCard
          label="Total Amount"
          :value="formatCurrency(vendor.total_invoice_amount ?? 0)"
          theme="emerald"
        >
          <template #icon>
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </template>
        </UiStatCard>

        <UiStatCard
          label="Status"
          :value="vendor.is_active ? 'Active' : 'Inactive'"
          :theme="vendor.is_active ? 'emerald' : 'gray'"
        >
          <template #icon>
            <svg :class="['w-5 h-5', vendor.is_active ? 'text-emerald-600' : 'text-gray-600']" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="vendor.is_active" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
          </template>
        </UiStatCard>
      </div>

      <!-- Details Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Contact Information -->
        <FormSection
          title="Contact Information"
          description="How to reach this vendor"
          theme="violet"
        >
          <template #icon>
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </template>

          <div v-if="vendor.email || vendor.phone || vendor.full_address" class="space-y-4">
            <div v-if="vendor.email" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
              <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Email</p>
                <a :href="`mailto:${vendor.email}`" class="text-sm font-medium text-gray-900 hover:text-violet-600">
                  {{ vendor.email }}
                </a>
              </div>
            </div>

            <div v-if="vendor.phone" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
              <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Phone</p>
                <a :href="`tel:${vendor.phone}`" class="text-sm font-medium text-gray-900 hover:text-emerald-600">
                  {{ vendor.phone }}
                </a>
              </div>
            </div>

            <div v-if="vendor.full_address" class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
              <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Address</p>
                <p class="text-sm font-medium text-gray-900">{{ vendor.full_address }}</p>
              </div>
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">No contact information available</p>
        </FormSection>

        <!-- Business Information -->
        <FormSection
          title="Business Information"
          description="Tax and payment details"
          theme="primary"
        >
          <template #icon>
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </template>

          <dl class="space-y-4">
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
              <dt class="text-sm text-gray-500">Tax ID</dt>
              <dd class="text-sm font-medium text-gray-900 font-mono bg-white px-2 py-1 rounded border">
                {{ vendor.tax_id || 'Not provided' }}
              </dd>
            </div>
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
              <dt class="text-sm text-gray-500">Payment Terms</dt>
              <dd class="text-sm font-medium text-gray-900">
                {{ vendor.payment_terms || 'Not specified' }}
              </dd>
            </div>
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
              <dt class="text-sm text-gray-500">Status</dt>
              <dd>
                <UiStatusBadge
                  :variant="vendor.is_active ? 'active' : 'inactive'"
                  :label="vendor.is_active ? 'Active' : 'Inactive'"
                />
              </dd>
            </div>
          </dl>
        </FormSection>

        <!-- Invoice Statistics -->
        <FormSection
          title="Invoice Summary"
          description="Financial overview"
          theme="emerald"
        >
          <template #icon>
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </template>

          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="p-4 bg-gray-50 rounded-lg text-center">
                <p class="text-2xl font-bold text-gray-900">{{ vendor.invoices_count ?? 0 }}</p>
                <p class="text-xs text-gray-500 uppercase tracking-wide mt-1">Total Invoices</p>
              </div>
              <div class="p-4 bg-amber-50 rounded-lg text-center">
                <p class="text-2xl font-bold text-amber-700">{{ vendor.pending_invoices_count ?? 0 }}</p>
                <p class="text-xs text-gray-500 uppercase tracking-wide mt-1">Pending</p>
              </div>
            </div>

            <div class="p-4 bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-lg border border-emerald-200">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wide">Total Invoice Amount</p>
                  <p class="text-xs text-gray-400 mt-0.5">All time</p>
                </div>
                <p class="text-2xl font-bold text-emerald-700">
                  {{ formatCurrency(vendor.total_invoice_amount ?? 0) }}
                </p>
              </div>
            </div>

            <NuxtLink
              :to="`/invoices?vendor_id=${vendor.id}`"
              class="flex items-center justify-center gap-2 w-full p-3 text-sm font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors"
            >
              View All Invoices
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
            </NuxtLink>
          </div>
        </FormSection>

        <!-- Notes -->
        <FormSection
          title="Notes"
          description="Additional information"
          theme="amber"
        >
          <template #icon>
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </template>

          <div v-if="vendor.notes" class="p-4 bg-amber-50 rounded-lg border border-amber-100">
            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ vendor.notes }}</p>
          </div>
          <p v-else class="text-sm text-gray-500 text-center py-8">No notes added for this vendor</p>
        </FormSection>
      </div>

      <!-- Timestamps -->
      <div class="mt-6">
        <FormSection
          title="Record Information"
          description="Creation and modification history"
          theme="rose"
        >
          <template #icon>
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </template>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
              <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Created</p>
                <p class="text-sm font-medium text-gray-900">{{ vendor.created_at }}</p>
              </div>
            </div>
            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
              <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Last Updated</p>
                <p class="text-sm font-medium text-gray-900">{{ vendor.updated_at }}</p>
              </div>
            </div>
          </div>
        </FormSection>
      </div>
    </div>
  </div>
</template>
