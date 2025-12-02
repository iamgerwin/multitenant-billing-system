<script setup lang="ts">
import type { VendorPayload, ApiErrorResponse } from '~/types'

definePageMeta({
  middleware: 'auth',
})

const router = useRouter()
const vendorStore = useVendorStore()

const form = reactive<VendorPayload>({
  name: '',
  code: null,
  email: null,
  phone: null,
  address: null,
  city: null,
  state: null,
  postal_code: null,
  country: null,
  tax_id: null,
  payment_terms: null,
  notes: null,
  is_active: true,
})

const errors = reactive<Record<string, string[]>>({})
const isSubmitting = ref(false)

const handleSubmit = async () => {
  isSubmitting.value = true
  Object.keys(errors).forEach((key) => delete errors[key])

  try {
    const vendor = await vendorStore.createVendor(form)
    if (vendor) {
      router.push(`/vendors/${vendor.id}`)
    }
  } catch (err) {
    const error = err as ApiErrorResponse
    if (error.errors) {
      Object.assign(errors, error.errors)
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div>
    <div class="mb-6">
      <NuxtLink to="/vendors" class="text-sm text-gray-500 hover:text-gray-700">
        &larr; Back to Vendors
      </NuxtLink>
      <h1 class="mt-2 text-2xl font-bold text-gray-900">Add Vendor</h1>
    </div>

    <form class="card p-6" @submit.prevent="handleSubmit">
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <!-- Name -->
        <div class="sm:col-span-2">
          <label for="name" class="label">Vendor Name *</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            class="input"
            :class="{ 'border-red-300': errors.name }"
            placeholder="Company Name"
          />
          <p v-if="errors.name" class="mt-1 text-sm text-red-600">
            {{ errors.name[0] }}
          </p>
        </div>

        <!-- Code -->
        <div>
          <label for="code" class="label">Vendor Code</label>
          <input
            id="code"
            v-model="form.code"
            type="text"
            class="input"
            :class="{ 'border-red-300': errors.code }"
            placeholder="VND-001"
          />
          <p v-if="errors.code" class="mt-1 text-sm text-red-600">
            {{ errors.code[0] }}
          </p>
        </div>

        <!-- Tax ID -->
        <div>
          <label for="tax_id" class="label">Tax ID</label>
          <input
            id="tax_id"
            v-model="form.tax_id"
            type="text"
            class="input"
            placeholder="Tax identification number"
          />
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="label">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="input"
            :class="{ 'border-red-300': errors.email }"
            placeholder="contact@vendor.com"
          />
          <p v-if="errors.email" class="mt-1 text-sm text-red-600">
            {{ errors.email[0] }}
          </p>
        </div>

        <!-- Phone -->
        <div>
          <label for="phone" class="label">Phone</label>
          <input
            id="phone"
            v-model="form.phone"
            type="text"
            class="input"
            placeholder="+1 (555) 123-4567"
          />
        </div>

        <!-- Address -->
        <div class="sm:col-span-2">
          <label for="address" class="label">Street Address</label>
          <input
            id="address"
            v-model="form.address"
            type="text"
            class="input"
            placeholder="123 Main St"
          />
        </div>

        <!-- City -->
        <div>
          <label for="city" class="label">City</label>
          <input
            id="city"
            v-model="form.city"
            type="text"
            class="input"
          />
        </div>

        <!-- State -->
        <div>
          <label for="state" class="label">State/Province</label>
          <input
            id="state"
            v-model="form.state"
            type="text"
            class="input"
          />
        </div>

        <!-- Postal Code -->
        <div>
          <label for="postal_code" class="label">Postal Code</label>
          <input
            id="postal_code"
            v-model="form.postal_code"
            type="text"
            class="input"
          />
        </div>

        <!-- Country -->
        <div>
          <label for="country" class="label">Country</label>
          <input
            id="country"
            v-model="form.country"
            type="text"
            class="input"
          />
        </div>

        <!-- Payment Terms -->
        <div>
          <label for="payment_terms" class="label">Payment Terms</label>
          <input
            id="payment_terms"
            v-model="form.payment_terms"
            type="text"
            class="input"
            placeholder="e.g., Net 30"
          />
        </div>

        <!-- Active Status -->
        <div class="flex items-center">
          <input
            id="is_active"
            v-model="form.is_active"
            type="checkbox"
            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
          />
          <label for="is_active" class="ml-2 block text-sm text-gray-900">
            Active vendor
          </label>
        </div>

        <!-- Notes -->
        <div class="sm:col-span-2">
          <label for="notes" class="label">Notes</label>
          <textarea
            id="notes"
            v-model="form.notes"
            rows="3"
            class="input"
            placeholder="Internal notes about this vendor..."
          />
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-6 flex items-center justify-end space-x-4">
        <NuxtLink to="/vendors" class="btn-secondary">
          Cancel
        </NuxtLink>
        <button
          type="submit"
          :disabled="isSubmitting"
          class="btn-primary"
          :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }"
        >
          {{ isSubmitting ? 'Creating...' : 'Create Vendor' }}
        </button>
      </div>
    </form>
  </div>
</template>
