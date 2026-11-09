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
    class="inline-flex items-center justify-center rounded-lg px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
    :class="props.variant === 'danger'
      ? 'bg-red-600 hover:bg-red-700 focus:ring-red-600'
      : 'bg-slate-900 hover:bg-slate-700 focus:ring-slate-900'"
  >
    <slot />
  </button>
</template>
