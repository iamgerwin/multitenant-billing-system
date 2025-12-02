<script setup lang="ts">
const config = useRuntimeConfig()
</script>

<template>
  <div class="flex min-h-screen items-center justify-center">
    <div class="text-center">
      <h1 class="text-4xl font-bold text-gray-900 mb-4">
        Multitenant Billing System
      </h1>
      <p class="text-gray-600 mb-8">
        Welcome to the billing management platform
      </p>
      <div class="space-x-4">
        <NuxtLink
          to="/login"
          class="btn-primary"
        >
          Sign In
        </NuxtLink>
      </div>
      <p class="mt-8 text-sm text-gray-500">
        API: {{ config.public.apiBaseUrl }}
      </p>
    </div>
  </div>
</template>
