<script setup lang="ts">
import type { TimeLabelCaParts } from '../../../shared/parseTimeLabel';

defineProps<{
  timeParts: TimeLabelCaParts;
  terminal: boolean;
}>();
</script>

<template>
  <time
    class="mrt-timeline__time"
    :class="{ 'is-terminal': terminal }"
  >
    <span class="mrt-timeline__time-stack">
      <span v-if="timeParts.ca" class="mrt-timeline__time-ca">Ca</span>
      <span class="mrt-timeline__time-value">{{ timeParts.value }}</span>
    </span>
  </time>
</template>

<style scoped>
.mrt-timeline__time {
  text-align: right;
  font-size: clamp(0.95rem, 2.2vw, 1.15rem);
  font-weight: 700;
}

.mrt-timeline__time:not(.is-terminal) {
  font-weight: 500;
}

.mrt-timeline__time-stack {
  display: inline-flex;
  flex-direction: row;
  align-items: baseline;
  justify-content: flex-end;
  line-height: 1.15;
  gap: 0.2em;
}

.mrt-timeline__time-ca {
  font-size: 0.85em;
  font-weight: 600;
  letter-spacing: 0.02em;
}
</style>
