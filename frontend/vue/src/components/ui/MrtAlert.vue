<script setup lang="ts">
import type { MrtAlertVariant } from './types';

withDefaults(
  defineProps<{
    variant?: MrtAlertVariant;
    live?: 'off' | 'polite' | 'assertive';
  }>(),
  {
    variant: 'info',
    live: 'off',
  },
);
</script>

<template>
  <div
    class="mrt-ui-alert"
    :class="`mrt-ui-alert--${variant}`"
    role="alert"
    :aria-live="live === 'off' ? undefined : live"
  >
    <slot />
  </div>
</template>
