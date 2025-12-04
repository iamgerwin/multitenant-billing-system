<script setup lang="ts">
interface Props {
  title: string
  description?: string
  actionLabel?: string
  actionLink?: string
}

defineProps<Props>()
</script>

<template>
  <div class="text-center py-12">
    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
      <slot name="icon">
        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </slot>
    </div>
    <h3 class="text-sm font-medium text-gray-900 mb-1">{{ title }}</h3>
    <p v-if="description" class="text-sm text-gray-500 mb-4">{{ description }}</p>
    <NuxtLink
      v-if="actionLabel && actionLink"
      :to="actionLink"
      class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors"
    >
      {{ actionLabel }}
    </NuxtLink>
    <slot name="action" />
  </div>
</template>
