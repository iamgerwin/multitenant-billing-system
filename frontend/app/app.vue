<script setup lang="ts">
// Use useState for SSR-safe state to prevent hydration mismatch
const isAppReady = useState('app-ready', () => false)

onMounted(() => {
  // Small delay to ensure CSS is fully loaded and applied
  nextTick(() => {
    isAppReady.value = true
  })
})
</script>

<template>
  <div class="app-wrapper">
    <!-- Loading overlay - always rendered, hidden when app is ready -->
    <div v-if="!isAppReady" class="loading-overlay">
      <div class="loading-spinner">
        <div class="loading-icon-wrapper">
          <svg class="loading-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle class="loading-circle" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
          </svg>
        </div>
        <span class="loading-text">Loading...</span>
      </div>
    </div>

    <!-- Main app content - hidden until ready -->
    <div :class="['app-content', { 'app-ready': isAppReady }]">
      <NuxtRouteAnnouncer />
      <NuxtLayout>
        <NuxtPage />
      </NuxtLayout>
    </div>
  </div>
</template>

<style>
/* Additional styles - critical CSS is in nuxt.config.ts head */
</style>
