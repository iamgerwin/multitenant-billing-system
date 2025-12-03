<script setup lang="ts">
type BadgeVariant = 'pending' | 'approved' | 'rejected' | 'paid' | 'active' | 'inactive' | 'overdue' | 'default'

interface Props {
  variant?: BadgeVariant
  label: string
  size?: 'sm' | 'md'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'sm',
})

const variantClasses = computed(() => {
  const variants: Record<BadgeVariant, string> = {
    pending: 'bg-amber-100 text-amber-800 ring-1 ring-amber-600/20',
    approved: 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-600/20',
    rejected: 'bg-rose-100 text-rose-800 ring-1 ring-rose-600/20',
    paid: 'bg-primary-100 text-primary-800 ring-1 ring-primary-600/20',
    active: 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-600/20',
    inactive: 'bg-gray-100 text-gray-800 ring-1 ring-gray-600/20',
    overdue: 'bg-rose-100 text-rose-800 ring-1 ring-rose-600/20',
    default: 'bg-gray-100 text-gray-800 ring-1 ring-gray-600/20',
  }
  return variants[props.variant]
})

const sizeClasses = computed(() => {
  return props.size === 'md' ? 'px-3 py-1 text-sm' : 'px-2.5 py-0.5 text-xs'
})
</script>

<template>
  <span
    class="inline-flex items-center rounded-full font-medium"
    :class="[variantClasses, sizeClasses]"
  >
    {{ label }}
  </span>
</template>
