<script setup lang="ts">
interface Props {
  modelValue: string | number | null
  label: string
  id: string
  type?: 'text' | 'email' | 'number' | 'date' | 'tel' | 'password'
  placeholder?: string
  required?: boolean
  readonly?: boolean
  disabled?: boolean
  error?: string | string[]
  hint?: string
  prefix?: string
  step?: string
  min?: string | number
  max?: string | number
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  required: false,
  readonly: false,
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

const inputClasses = computed(() => [
  'block w-full py-3 rounded-lg border shadow-sm transition-colors',
  'focus:ring-2 focus:outline-none',
  props.prefix ? 'pl-8 pr-4' : 'px-4',
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
    <div class="relative">
      <span
        v-if="prefix"
        class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500 pointer-events-none"
      >
        {{ prefix }}
      </span>
      <input
        :id="id"
        v-model="inputValue"
        :type="type"
        :placeholder="placeholder"
        :readonly="readonly"
        :disabled="disabled"
        :step="step"
        :min="min"
        :max="max"
        :class="inputClasses"
      />
      <slot name="suffix" />
    </div>
    <p v-if="hint && !hasError" class="text-xs text-gray-500">{{ hint }}</p>
    <p v-if="hasError" class="text-sm text-red-600">{{ errorMessage }}</p>
  </div>
</template>
