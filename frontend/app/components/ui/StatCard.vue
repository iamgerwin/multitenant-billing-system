<script setup lang="ts">
type ColorTheme = 'primary' | 'emerald' | 'amber' | 'rose' | 'violet' | 'gray'

interface Props {
  label: string
  value: string | number
  theme?: ColorTheme
  sublabel?: string
}

const props = withDefaults(defineProps<Props>(), {
  theme: 'primary',
})

const themeClasses = computed(() => {
  const themes: Record<ColorTheme, { bg: string; icon: string }> = {
    primary: { bg: 'bg-primary-100', icon: 'text-primary-600' },
    emerald: { bg: 'bg-emerald-100', icon: 'text-emerald-600' },
    amber: { bg: 'bg-amber-100', icon: 'text-amber-600' },
    rose: { bg: 'bg-rose-100', icon: 'text-rose-600' },
    violet: { bg: 'bg-violet-100', icon: 'text-violet-600' },
    gray: { bg: 'bg-gray-100', icon: 'text-gray-600' },
  }
  return themes[props.theme]
})
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <div class="flex items-center">
      <div
        class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
        :class="themeClasses.bg"
      >
        <slot name="icon">
          <svg :class="['w-5 h-5', themeClasses.icon]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </slot>
      </div>
      <div class="ml-3">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ label }}</p>
        <p class="text-xl font-bold text-gray-900">{{ value }}</p>
        <p v-if="sublabel" class="text-xs text-gray-400">{{ sublabel }}</p>
      </div>
    </div>
  </div>
</template>
