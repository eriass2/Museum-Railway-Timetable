<script setup lang="ts">
import type { MrtDotColor } from './types';

defineProps<{
  color: MrtDotColor;
}>();
</script>

<template>
  <span class="mrt-dot" :class="`mrt-dot--${color}`" aria-hidden="true" />
</template>

<style scoped>
.mrt-dot {
  display: inline-block;
  width: var(--mrt-size-dot);
  height: var(--mrt-size-dot);
  border-radius: 50%;
}

.mrt-dot--green {
  background: var(--mrt-color-traffic-green);
  border: 1px solid var(--mrt-color-green-800);
}

.mrt-dot--yellow {
  background: var(--mrt-color-traffic-yellow);
  border: 1px solid var(--mrt-color-accent-700);
}

.mrt-dot--red {
  background: var(--mrt-color-traffic-red);
  border: 1px solid #8a1a12;
}

.mrt-dot--orange {
  background: var(--mrt-color-traffic-orange);
  border: 1px solid #9a5a08;
}

.mrt-dot--blue {
  background: var(--mrt-blue-primary);
  border: 1px solid #135a96;
}
</style>
