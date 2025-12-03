<script setup lang="ts">
interface Props {
  title: string
  description?: string
  backLink?: string
  backLabel?: string
}

const props = withDefaults(defineProps<Props>(), {
  backLabel: 'Back',
})
</script>

<template>
  <div class="mb-8">
    <NuxtLink
      v-if="backLink"
      :to="backLink"
      class="inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors"
    >
      <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      {{ backLabel }}
    </NuxtLink>
    <h1 :class="['text-3xl font-bold text-gray-900', backLink ? 'mt-4' : '']">{{ title }}</h1>
    <p v-if="description" class="mt-1 text-sm text-gray-500">{{ description }}</p>
  </div>
</template>
