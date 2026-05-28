<script setup lang="ts">
withDefaults(
  defineProps<{
    href?: string;
    type?: 'button' | 'submit';
    variant?: 'primary' | 'select' | 'secondary';
    disabled?: boolean;
  }>(),
  {
    type: 'button',
    variant: 'primary',
    disabled: false,
  },
);

defineEmits<{ click: [] }>();
</script>

<template>
  <a
    v-if="href"
    :href="href"
    class="mrt-accent-btn"
    :class="`mrt-accent-btn--${variant}`"
  >
    <slot />
  </a>
  <button
    v-else
    :type="type"
    class="mrt-accent-btn"
    :class="`mrt-accent-btn--${variant}`"
    :disabled="disabled"
    @click="$emit('click')"
  >
    <slot />
  </button>
</template>
