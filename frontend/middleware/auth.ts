export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthStore()

  authStore.initialize()

  if (!authStore.isAuthenticated) {
    return navigateTo('/login')
  }
})
