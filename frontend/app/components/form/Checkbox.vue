<script setup lang="ts">
interface Props {
  modelValue: boolean
  label: string
  id: string
  description?: string
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const inputValue = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})
</script>

<template>
  <div class="flex items-start">
    <div class="flex items-center h-6">
      <input
        :id="id"
        v-model="inputValue"
        type="checkbox"
        :disabled="disabled"
        class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded transition-colors cursor-pointer disabled:cursor-not-allowed disabled:opacity-50"
      />
    </div>
    <div class="ml-3">
      <label :for="id" class="text-sm font-medium text-gray-900 cursor-pointer">
        {{ label }}
      </label>
      <p v-if="description" class="text-xs text-gray-500">{{ description }}</p>
    </div>
  </div>
</template>
