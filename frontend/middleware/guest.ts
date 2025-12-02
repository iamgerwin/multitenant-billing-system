export default defineNuxtRouteMiddleware(() => {
  const authStore = useAuthStore()

  authStore.initialize()

  if (authStore.isAuthenticated) {
    return navigateTo('/dashboard')
  }
})
