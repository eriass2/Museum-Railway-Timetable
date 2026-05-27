<script setup lang="ts">
withDefaults(
  defineProps<{
    href?: string;
    type?: 'button' | 'submit';
    variant?: 'primary' | 'select';
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
    class="mrt-journey-wizard__accent-btn"
    :class="`mrt-journey-wizard__accent-btn--${variant}`"
  >
    <slot />
  </a>
  <button
    v-else
    :type="type"
    class="mrt-journey-wizard__accent-btn"
    :class="`mrt-journey-wizard__accent-btn--${variant}`"
    :disabled="disabled"
    @click="$emit('click')"
  >
    <slot />
  </button>
</template>
