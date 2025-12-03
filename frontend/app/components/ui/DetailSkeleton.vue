<script setup lang="ts">
interface Props {
  sections?: number
}

withDefaults(defineProps<Props>(), {
  sections: 4,
})
</script>

<template>
  <div class="max-w-7xl mx-auto animate-pulse">
    <!-- Header Skeleton -->
    <div class="mb-8">
      <div class="h-4 w-32 bg-gray-200 rounded mb-4" />
      <div class="h-8 w-64 bg-gray-200 rounded mb-2" />
      <div class="h-4 w-48 bg-gray-200 rounded" />
    </div>

    <!-- Stats Cards Skeleton -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div v-for="i in 4" :key="i" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-gray-200 rounded-lg" />
          <div class="ml-3 space-y-2">
            <div class="h-3 w-16 bg-gray-200 rounded" />
            <div class="h-6 w-12 bg-gray-200 rounded" />
          </div>
        </div>
      </div>
    </div>

    <!-- Content Sections Skeleton -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div
        v-for="i in sections"
        :key="i"
        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
      >
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-gray-200 rounded-lg" />
            <div class="ml-4 space-y-2">
              <div class="h-5 w-32 bg-gray-200 rounded" />
              <div class="h-3 w-24 bg-gray-200 rounded" />
            </div>
          </div>
        </div>
        <div class="p-6 space-y-4">
          <div class="flex justify-between">
            <div class="h-4 w-24 bg-gray-200 rounded" />
            <div class="h-4 w-32 bg-gray-200 rounded" />
          </div>
          <div class="flex justify-between">
            <div class="h-4 w-20 bg-gray-200 rounded" />
            <div class="h-4 w-28 bg-gray-200 rounded" />
          </div>
          <div class="flex justify-between">
            <div class="h-4 w-28 bg-gray-200 rounded" />
            <div class="h-4 w-20 bg-gray-200 rounded" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
