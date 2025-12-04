<script setup lang="ts">
// Use useState for SSR-safe state to prevent hydration mismatch
const isAppReady = useState('app-ready', () => false)

onMounted(() => {
  // Small delay to ensure CSS is fully loaded and applied
  nextTick(() => {
    isAppReady.value = true
  })
})

// Inline styles for SSR - ensures content is hidden before any CSS loads
const contentStyle = computed(() => ({
  opacity: isAppReady.value ? 1 : 0,
  visibility: isAppReady.value ? 'visible' : 'hidden',
  transition: 'opacity 0.2s ease',
}))

const overlayStyle = {
  position: 'fixed',
  inset: 0,
  zIndex: 9999,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  backgroundColor: '#f9fafb',
}

const spinnerStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  gap: '1rem',
}

const iconWrapperStyle = {
  width: '3rem',
  height: '3rem',
}

const iconStyle = {
  width: '100%',
  height: '100%',
  color: '#6366f1',
  animation: 'spin 1s linear infinite',
}

const textStyle = {
  fontSize: '0.875rem',
  fontWeight: 500,
  color: '#6b7280',
}
</script>

<template>
  <div style="min-height: 100vh; background-color: #f9fafb;">
    <!-- Loading overlay with inline styles - guaranteed to render correctly -->
    <div v-if="!isAppReady" :style="overlayStyle">
      <div :style="spinnerStyle">
        <div :style="iconWrapperStyle">
          <svg :style="iconStyle" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle style="stroke-dasharray: 60; stroke-dashoffset: 45; stroke-linecap: round;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
          </svg>
        </div>
        <span :style="textStyle">Loading...</span>
      </div>
    </div>

    <!-- Main app content - inline style ensures it's hidden before CSS loads -->
    <div :style="contentStyle">
      <NuxtRouteAnnouncer />
      <NuxtLayout>
        <NuxtPage />
      </NuxtLayout>
    </div>
  </div>
</template>

<style>
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
