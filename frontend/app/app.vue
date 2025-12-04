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
  <div class="min-h-screen bg-gray-50">
    <!-- Loading overlay - client only to prevent hydration mismatch -->
    <ClientOnly>
      <Transition name="fade">
        <div
          v-if="!isAppReady"
          class="loading-overlay"
        >
          <div class="loading-spinner">
            <svg class="loading-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle class="loading-circle" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
            </svg>
            <span class="loading-text">Loading...</span>
          </div>
        </div>
      </Transition>
    </ClientOnly>

    <!-- Main app content -->
    <NuxtRouteAnnouncer />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<style>
/* Critical CSS for loading overlay - inline to prevent FOUC */
.loading-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f9fafb;
}

.loading-spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.loading-icon {
  width: 3rem;
  height: 3rem;
  color: #6366f1;
  animation: spin 1s linear infinite;
}

.loading-circle {
  stroke-dasharray: 60;
  stroke-dashoffset: 45;
  stroke-linecap: round;
}

.loading-text {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
}

/* Fade transition for loading overlay */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Prevent SVG layout shifts by setting default dimensions */
svg {
  flex-shrink: 0;
}
</style>
