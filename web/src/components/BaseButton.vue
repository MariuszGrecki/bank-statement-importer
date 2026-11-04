<script setup lang="ts">

interface Props {
  type?: 'button' | 'submit' | 'reset'
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  type: 'button',
  disabled: false
})
</script>

<template>
  <button
    :type="props.type"
    :disabled="props.disabled"
    class="border p-2 rounded-xl bg-black text-white hover:bg-gray-800 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
    <slot />
  </button>
</template>
