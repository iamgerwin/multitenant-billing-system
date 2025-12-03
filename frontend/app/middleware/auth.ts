export default defineNuxtRouteMiddleware((to) => {
  // Skip auth check during SSR - localStorage is only available on client
  if (import.meta.server) {
    return
  }

  const authStore = useAuthStore()

  authStore.initialize()

  if (!authStore.isAuthenticated) {
    return navigateTo('/login')
  }
})
