<script setup lang="ts">
interface SelectOption {
  value: string | number
  label: string
  disabled?: boolean
}

interface Props {
  modelValue: string | number | null
  label: string
  id: string
  options: SelectOption[]
  placeholder?: string
  required?: boolean
  disabled?: boolean
  error?: string | string[]
  hint?: string
}

const props = withDefaults(defineProps<Props>(), {
  required: false,
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | number | null]
}>()

const inputValue = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})

const errorMessage = computed(() => {
  if (Array.isArray(props.error)) {
    return props.error[0]
  }
  return props.error
})

const hasError = computed(() => !!errorMessage.value)

const selectClasses = computed(() => [
  'block w-full px-4 py-3 rounded-lg border shadow-sm transition-colors',
  'focus:ring-2 focus:outline-none',
  props.disabled ? 'bg-gray-50 text-gray-600' : 'bg-white',
  hasError.value
    ? 'border-red-300 focus:ring-red-500 focus:border-red-500'
    : 'border-gray-300 focus:ring-primary-500 focus:border-primary-500',
])
</script>

<template>
  <div class="space-y-2">
    <label :for="id" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <select
      :id="id"
      v-model="inputValue"
      :disabled="disabled"
      :class="selectClasses"
    >
      <option v-if="placeholder" :value="null" disabled>{{ placeholder }}</option>
      <option
        v-for="option in options"
        :key="option.value"
        :value="option.value"
        :disabled="option.disabled"
      >
        {{ option.label }}
      </option>
    </select>
    <p v-if="hint && !hasError" class="text-xs text-gray-500">{{ hint }}</p>
    <p v-if="hasError" class="text-sm text-red-600">{{ errorMessage }}</p>
  </div>
</template>
