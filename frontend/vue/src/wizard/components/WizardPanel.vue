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
    class="mrt-journey-wizard__panel mrt-journey-wizard__panel--active"
    :class="{
      'mrt-journey-wizard__search-panel': variant === 'search',
      'mrt-journey-wizard__panel--wide': variant === 'wide',
    }"
    role="region"
    :aria-label="ariaLabel"
  >
    <slot />
  </div>
</template>
