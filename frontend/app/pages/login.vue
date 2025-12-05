<script setup lang="ts">
definePageMeta({
  layout: false,
  middleware: 'guest',
})

useHead({
  title: 'Welcome',
})

const authStore = useAuthStore()
const router = useRouter()

const form = reactive({
  email: '',
  password: '',
})

const errors = reactive<Record<string, string>>({})
const isSubmitting = ref(false)
const showPassword = ref(false)

const validateForm = (): boolean => {
  errors.email = ''
  errors.password = ''

  let isValid = true

  if (!form.email) {
    errors.email = 'Email is required'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Please enter a valid email'
    isValid = false
  }

  if (!form.password) {
    errors.password = 'Password is required'
    isValid = false
  }

  return isValid
}

const handleSubmit = async () => {
  if (!validateForm()) return

  isSubmitting.value = true

  const success = await authStore.login({
    email: form.email,
    password: form.password,
  })

  isSubmitting.value = false

  if (success) {
    router.push('/dashboard')
  }
}

onMounted(() => {
  if (authStore.isAuthenticated) {
    router.push('/dashboard')
  }
})
</script>

<template>
  <div class="min-h-screen flex">
    <!-- Left Side - Branding -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary-600 via-primary-700 to-violet-700 p-12 flex-col justify-between relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
          <defs>
            <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
              <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5" />
            </pattern>
          </defs>
          <rect width="100" height="100" fill="url(#grid)" />
        </svg>
      </div>

      <!-- Floating Elements -->
      <div class="absolute top-20 right-20 w-64 h-64 bg-white/10 rounded-full blur-3xl" />
      <div class="absolute bottom-20 left-10 w-96 h-96 bg-violet-500/20 rounded-full blur-3xl" />

      <!-- Logo & Title -->
      <div class="relative z-10">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <span class="text-2xl font-bold text-white">Billing System</span>
        </div>
      </div>

      <!-- Features -->
      <div class="relative z-10 space-y-8">
        <h1 class="text-4xl font-bold text-white leading-tight">
          Multi-Tenant<br />Invoice Management
        </h1>
        <p class="text-lg text-primary-100 max-w-md">
          Streamline your billing workflow with powerful invoice management, vendor tracking, and approval workflows.
        </p>

        <div class="space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <span class="text-white/90">Create and manage invoices effortlessly</span>
          </div>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <span class="text-white/90">Track vendors and their invoices</span>
          </div>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
            </div>
            <span class="text-white/90">Multi-level approval workflows</span>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="relative z-10">
        <p class="text-primary-200 text-sm">
          <a href="https://github.com/iamgerwin" target="_blank" class="hover:text-white transition-colors">Created by: John Gerwin De las Alas</a>
        </p>
      </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="flex-1 flex items-center justify-center p-8 bg-gray-50">
      <div class="w-full max-w-md">
        <!-- Mobile Logo -->
        <div class="lg:hidden mb-8 text-center">
          <div class="flex items-center justify-center gap-3 mb-4">
            <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center">
              <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <span class="text-2xl font-bold text-gray-900">Billing System</span>
          </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8">
          <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
            <p class="mt-2 text-sm text-gray-500">Sign in to access your dashboard</p>
          </div>

          <!-- Error Alert -->
          <div
            v-if="authStore.error"
            class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-xl flex items-start gap-3"
          >
            <div class="flex-shrink-0 w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-rose-800">Authentication failed</p>
              <p class="text-sm text-rose-600 mt-0.5">{{ authStore.error }}</p>
            </div>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Email Field -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email address
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                </div>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  autocomplete="email"
                  placeholder="you@example.com"
                  class="block w-full pl-12 pr-4 py-3 rounded-xl border transition-colors text-sm"
                  :class="errors.email
                    ? 'border-rose-300 focus:ring-2 focus:ring-rose-500 focus:border-rose-500'
                    : 'border-gray-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500'"
                />
              </div>
              <p v-if="errors.email" class="mt-2 text-sm text-rose-600 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ errors.email }}
              </p>
            </div>

            <!-- Password Field -->
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                Password
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="current-password"
                  placeholder="Enter your password"
                  class="block w-full pl-12 pr-12 py-3 rounded-xl border transition-colors text-sm"
                  :class="errors.password
                    ? 'border-rose-300 focus:ring-2 focus:ring-rose-500 focus:border-rose-500'
                    : 'border-gray-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500'"
                />
                <button
                  type="button"
                  class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600"
                  @click="showPassword = !showPassword"
                >
                  <svg v-if="showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
              <p v-if="errors.password" class="mt-2 text-sm text-rose-600 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ errors.password }}
              </p>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="isSubmitting || authStore.isLoading"
              class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-primary-600 to-primary-700 text-white font-medium rounded-xl hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-primary-500/25"
            >
              <svg
                v-if="isSubmitting || authStore.isLoading"
                class="animate-spin h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
              </svg>
              <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
              </svg>
              {{ isSubmitting ? 'Signing in...' : 'Sign in' }}
            </button>
          </form>

          <!-- Demo Credentials -->
          <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-500 text-center mb-3">Demo credentials</p>

            <!-- Demo Company -->
            <div class="mb-4">
              <p class="text-xs font-semibold text-primary-600 mb-2 flex items-center gap-1">
                <span class="w-2 h-2 bg-primary-500 rounded-full"></span>
                Demo Company
              </p>
              <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="p-2 bg-gray-50 rounded-lg">
                  <p class="font-medium text-gray-700">Admin</p>
                  <p class="text-gray-500 mt-0.5 truncate">admin@demo.com</p>
                </div>
                <div class="p-2 bg-gray-50 rounded-lg">
                  <p class="font-medium text-gray-700">Accountant</p>
                  <p class="text-gray-500 mt-0.5 truncate">accountant@demo.com</p>
                </div>
              </div>
            </div>

            <!-- Acme Corp -->
            <div>
              <p class="text-xs font-semibold text-violet-600 mb-2 flex items-center gap-1">
                <span class="w-2 h-2 bg-violet-500 rounded-full"></span>
                Acme Corp
              </p>
              <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="p-2 bg-gray-50 rounded-lg">
                  <p class="font-medium text-gray-700">Admin</p>
                  <p class="text-gray-500 mt-0.5 truncate">admin@acme.com</p>
                </div>
                <div class="p-2 bg-gray-50 rounded-lg">
                  <p class="font-medium text-gray-700">Accountant</p>
                  <p class="text-gray-500 mt-0.5 truncate">accountant@acme.com</p>
                </div>
              </div>
            </div>

            <p class="text-xs text-gray-400 text-center mt-4">Password for all: <code class="bg-gray-100 px-1.5 py-0.5 rounded">password</code></p>
          </div>
        </div>

        <!-- Footer -->
        <p class="mt-8 text-center text-sm text-gray-500">
          Secure login protected by industry-standard encryption
        </p>
      </div>
    </div>
  </div>
</template>
