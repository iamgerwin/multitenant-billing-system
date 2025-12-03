<script setup lang="ts">
interface Props {
  modelValue: string | null
  label: string
  id: string
  placeholder?: string
  required?: boolean
  readonly?: boolean
  disabled?: boolean
  error?: string | string[]
  hint?: string
  rows?: number
}

const props = withDefaults(defineProps<Props>(), {
  required: false,
  readonly: false,
  disabled: false,
  rows: 3,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | null]
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

const textareaClasses = computed(() => [
  'block w-full px-4 py-3 rounded-lg border shadow-sm transition-colors resize-none',
  'focus:ring-2 focus:outline-none',
  props.readonly || props.disabled ? 'bg-gray-50 text-gray-600' : 'bg-white',
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
    <textarea
      :id="id"
      v-model="inputValue"
      :placeholder="placeholder"
      :readonly="readonly"
      :disabled="disabled"
      :rows="rows"
      :class="textareaClasses"
    />
    <p v-if="hint && !hasError" class="text-xs text-gray-500">{{ hint }}</p>
    <p v-if="hasError" class="text-sm text-red-600">{{ errorMessage }}</p>
  </div>
</template>
