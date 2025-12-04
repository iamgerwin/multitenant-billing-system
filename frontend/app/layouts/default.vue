<script setup lang="ts">
const route = useRoute()
const authStore = useAuthStore()

const isMobileMenuOpen = ref(false)

const navigation = computed(() => [
  { name: 'Dashboard', href: '/dashboard', icon: 'home' },
  { name: 'Invoices', href: '/invoices', icon: 'document' },
  { name: 'Vendors', href: '/vendors', icon: 'building' },
])

const isActive = (href: string): boolean => {
  return route.path === href || route.path.startsWith(`${href}/`)
}

const handleLogout = async () => {
  await authStore.logout()
}

onMounted(() => {
  authStore.initialize()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile menu overlay -->
    <div
      v-if="isMobileMenuOpen"
      class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
      @click="isMobileMenuOpen = false"
    />

    <!-- Mobile sidebar -->
    <div
      v-if="isMobileMenuOpen"
      class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:hidden"
    >
      <div class="flex items-center justify-between h-16 px-4 border-b">
        <div class="min-w-0">
          <span class="text-lg font-semibold text-gray-900">Billing System</span>
          <p v-if="authStore.currentUser?.organization" class="text-xs text-gray-500 truncate">
            {{ authStore.currentUser.organization.name }}
          </p>
        </div>
        <button
          class="text-gray-400 hover:text-gray-500"
          @click="isMobileMenuOpen = false"
        >
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <nav class="px-2 py-4">
        <NuxtLink
          v-for="item in navigation"
          :key="item.name"
          :to="item.href"
          class="flex items-center px-4 py-2 text-sm font-medium rounded-md mb-1"
          :class="isActive(item.href) ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50'"
          @click="isMobileMenuOpen = false"
        >
          <!-- Home icon -->
          <svg v-if="item.icon === 'home'" class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <!-- Document icon -->
          <svg v-if="item.icon === 'document'" class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <!-- Building icon -->
          <svg v-if="item.icon === 'building'" class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          {{ item.name }}
        </NuxtLink>
      </nav>
    </div>

    <!-- Desktop sidebar -->
    <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
      <div class="flex flex-col flex-grow bg-white border-r border-gray-200">
        <div class="flex items-center h-16 px-4 border-b">
          <NuxtLink to="/dashboard" class="min-w-0">
            <span class="text-lg font-semibold text-gray-900">Billing System</span>
            <p v-if="authStore.currentUser?.organization" class="text-xs text-gray-500 truncate">
              {{ authStore.currentUser.organization.name }}
            </p>
          </NuxtLink>
        </div>
        <nav class="flex-1 px-2 py-4 space-y-1">
          <NuxtLink
            v-for="item in navigation"
            :key="item.name"
            :to="item.href"
            class="flex items-center px-4 py-2 text-sm font-medium rounded-md"
            :class="isActive(item.href) ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-50'"
          >
            <!-- Home icon -->
            <svg v-if="item.icon === 'home'" class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <!-- Document icon -->
            <svg v-if="item.icon === 'document'" class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <!-- Building icon -->
            <svg v-if="item.icon === 'building'" class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            {{ item.name }}
          </NuxtLink>
        </nav>
        <div class="flex-shrink-0 p-4 border-t">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center">
                <span class="text-sm font-medium text-primary-700">
                  {{ authStore.currentUser?.name?.charAt(0)?.toUpperCase() || 'U' }}
                </span>
              </div>
            </div>
            <div class="ml-3 min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-700 truncate">
                {{ authStore.currentUser?.name }}
              </p>
              <p class="text-xs text-gray-500 truncate">
                {{ authStore.currentUser?.role_label }}
              </p>
            </div>
            <button
              class="ml-2 p-1 text-gray-400 hover:text-gray-500"
              title="Logout"
              @click="handleLogout"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <div class="lg:pl-64">
      <!-- Top header for mobile -->
      <div class="sticky top-0 z-30 flex items-center h-16 px-4 bg-white border-b border-gray-200 lg:hidden">
        <button
          class="text-gray-500 hover:text-gray-600"
          @click="isMobileMenuOpen = true"
        >
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <div class="ml-4 min-w-0 flex-1">
          <span class="text-lg font-semibold text-gray-900">Billing System</span>
          <p v-if="authStore.currentUser?.organization" class="text-xs text-gray-500 truncate">
            {{ authStore.currentUser.organization.name }}
          </p>
        </div>
        <div class="ml-auto">
          <button
            class="p-1 text-gray-400 hover:text-gray-500"
            title="Logout"
            @click="handleLogout"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Page content -->
      <main class="py-6 px-4 sm:px-6 lg:px-8">
        <slot />
      </main>
    </div>
  </div>
</template>
