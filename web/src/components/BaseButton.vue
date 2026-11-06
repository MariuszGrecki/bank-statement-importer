<script setup lang="ts">

interface Props {
  type?: 'button' | 'submit' | 'reset'
  disabled?: boolean
  variant?: 'primary' | 'danger'
}

const props = withDefaults(defineProps<Props>(), {
  type: 'button',
  disabled: false,
  variant: 'primary'
})
</script>

<template>
  <button
    :type="props.type"
    :disabled="props.disabled"
    class="border p-2 rounded-xl bg-black text-white hover:bg-gray-800 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
    :class="props.variant === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-black hover:bg-gray-800'"
  >
    <slot />
  </button>
</template>
