<script setup lang="ts">
type ColorTheme = 'primary' | 'emerald' | 'amber' | 'rose' | 'violet'

interface Props {
  title: string
  description?: string
  theme?: ColorTheme
}

const props = withDefaults(defineProps<Props>(), {
  theme: 'primary',
})

const themeClasses = computed(() => {
  const themes: Record<ColorTheme, { header: string; icon: string; border: string }> = {
    primary: {
      header: 'from-primary-50 to-primary-100 border-primary-200',
      icon: 'bg-primary-600',
      border: 'border-primary-200',
    },
    emerald: {
      header: 'from-emerald-50 to-emerald-100 border-emerald-200',
      icon: 'bg-emerald-600',
      border: 'border-emerald-200',
    },
    amber: {
      header: 'from-amber-50 to-amber-100 border-amber-200',
      icon: 'bg-amber-600',
      border: 'border-amber-200',
    },
    rose: {
      header: 'from-rose-50 to-rose-100 border-rose-200',
      icon: 'bg-rose-600',
      border: 'border-rose-200',
    },
    violet: {
      header: 'from-violet-50 to-violet-100 border-violet-200',
      icon: 'bg-violet-600',
      border: 'border-violet-200',
    },
  }
  return themes[props.theme]
})
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div
      class="px-6 py-4 bg-gradient-to-r border-b"
      :class="[themeClasses.header, themeClasses.border]"
    >
      <div class="flex items-center">
        <div
          class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
          :class="themeClasses.icon"
        >
          <slot name="icon">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </slot>
        </div>
        <div class="ml-4">
          <h2 class="text-lg font-semibold text-gray-900">{{ title }}</h2>
          <p v-if="description" class="text-sm text-gray-600">{{ description }}</p>
        </div>
      </div>
    </div>
    <div class="p-6">
      <slot />
    </div>
  </div>
</template>
