<script setup lang="ts">
withDefaults(
  defineProps<{
    step: string;
    ariaLabel: string;
    variant?: 'default' | 'search' | 'wide';
  }>(),
  { variant: 'default' },
);
</script>

<template>
  <div
    :data-wizard-step="step"
    class="mrt-step-panel mrt-step-panel--active"
    :class="{
      'mrt-step-panel--search': variant === 'search',
      'mrt-step-panel--wide': variant === 'wide',
    }"
    role="region"
    :aria-label="ariaLabel"
  >
    <slot />
  </div>
</template>
