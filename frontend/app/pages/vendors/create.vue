<script setup lang="ts">
import type { VendorPayload, ApiErrorResponse } from '@billing/shared'

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
  <div class="max-w-4xl mx-auto">
    <UiHeader
      title="Add Vendor"
      description="Fill in the details below to create a new vendor"
      back-link="/vendors"
      back-label="Back to Vendors"
    />

    <form @submit.prevent="handleSubmit" class="space-y-8">
      <!-- Section 1: Basic Information -->
      <FormSection
        title="Basic Information"
        description="Vendor identity and contact details"
        theme="primary"
      >
        <template #icon>
          <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
          <div class="md:col-span-2">
            <FormInput
              id="name"
              v-model="form.name"
              label="Vendor Name"
              placeholder="Company Name"
              :required="true"
              :error="errors.name"
            />
          </div>

          <FormInput
            id="code"
            v-model="form.code"
            label="Vendor Code"
            placeholder="VND-001"
            hint="Unique identifier for this vendor"
            :error="errors.code"
          />

          <FormInput
            id="tax_id"
            v-model="form.tax_id"
            label="Tax ID"
            placeholder="Tax identification number"
          />

          <FormInput
            id="email"
            v-model="form.email"
            label="Email"
            type="email"
            placeholder="contact@vendor.com"
            :error="errors.email"
          />

          <FormInput
            id="phone"
            v-model="form.phone"
            label="Phone"
            type="tel"
            placeholder="+1 (555) 123-4567"
          />
        </div>
      </FormSection>

      <!-- Section 2: Address -->
      <FormSection
        title="Address"
        description="Vendor's physical location"
        theme="emerald"
      >
        <template #icon>
          <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
          <div class="md:col-span-2">
            <FormInput
              id="address"
              v-model="form.address"
              label="Street Address"
              placeholder="123 Main St"
            />
          </div>

          <FormInput
            id="city"
            v-model="form.city"
            label="City"
            placeholder="New York"
          />

          <FormInput
            id="state"
            v-model="form.state"
            label="State/Province"
            placeholder="NY"
          />

          <FormInput
            id="postal_code"
            v-model="form.postal_code"
            label="Postal Code"
            placeholder="10001"
          />

          <FormInput
            id="country"
            v-model="form.country"
            label="Country"
            placeholder="United States"
          />
        </div>
      </FormSection>

      <!-- Section 3: Payment & Notes -->
      <FormSection
        title="Payment & Notes"
        description="Payment terms and additional information"
        theme="amber"
      >
        <template #icon>
          <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
          <FormInput
            id="payment_terms"
            v-model="form.payment_terms"
            label="Payment Terms"
            placeholder="e.g., Net 30"
            hint="Standard payment terms for this vendor"
          />

          <div class="flex items-end pb-2">
            <FormCheckbox
              id="is_active"
              v-model="form.is_active"
              label="Active Vendor"
              description="Inactive vendors won't appear in selection lists"
            />
          </div>

          <div class="md:col-span-2">
            <FormTextarea
              id="notes"
              v-model="form.notes"
              label="Internal Notes"
              placeholder="Add any internal notes about this vendor..."
              hint="For internal use only"
              :rows="3"
            />
          </div>
        </div>
      </FormSection>

      <!-- Actions -->
      <FormActions
        cancel-link="/vendors"
        submit-label="Create Vendor"
        submitting-label="Creating..."
        :is-submitting="isSubmitting"
      />
    </form>
  </div>
</template>
