<script setup lang="ts">
interface DetailItem {
  label: string
  value: string | number | null | undefined
  icon?: string
  highlight?: boolean
  href?: string
}

interface Props {
  items: DetailItem[]
  emptyText?: string
}

const props = withDefaults(defineProps<Props>(), {
  emptyText: 'No information available',
})

const visibleItems = computed(() => {
  return props.items.filter((item) => item.value !== null && item.value !== undefined && item.value !== '')
})
</script>

<template>
  <dl v-if="visibleItems.length > 0" class="space-y-4">
    <div
      v-for="(item, index) in visibleItems"
      :key="index"
      class="flex justify-between items-start"
      :class="{ 'pt-4 border-t border-gray-200': item.highlight }"
    >
      <dt class="text-sm text-gray-500 flex items-center gap-2">
        <slot :name="`icon-${index}`" />
        {{ item.label }}
      </dt>
      <dd
        class="text-sm text-right"
        :class="item.highlight ? 'font-semibold text-gray-900' : 'text-gray-900'"
      >
        <NuxtLink
          v-if="item.href"
          :to="item.href"
          class="text-primary-600 hover:text-primary-700"
        >
          {{ item.value }}
        </NuxtLink>
        <span v-else>{{ item.value }}</span>
      </dd>
    </div>
  </dl>
  <p v-else class="text-sm text-gray-500">{{ emptyText }}</p>
</template>
