<script setup lang="ts">
interface Props {
  cancelLink: string
  submitLabel?: string
  submittingLabel?: string
  isSubmitting?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  submitLabel: 'Submit',
  submittingLabel: 'Submitting...',
  isSubmitting: false,
})
</script>

<template>
  <div class="flex items-center justify-end gap-4 pt-4">
    <NuxtLink
      :to="cancelLink"
      class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
    >
      Cancel
    </NuxtLink>
    <button
      type="submit"
      :disabled="isSubmitting"
      class="px-6 py-3 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-lg shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
    >
      <span v-if="isSubmitting" class="flex items-center">
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
        </svg>
        {{ submittingLabel }}
      </span>
      <span v-else>{{ submitLabel }}</span>
    </button>
  </div>
</template>
