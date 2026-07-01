<script setup lang="ts">
import type { LineSegment } from './types';

defineProps<{
  segment: LineSegment;
}>();
</script>

<template>
  <span
    class="mrt-timeline__node-col"
    :class="`mrt-timeline__node-col--segment-${segment}`"
    aria-hidden="true"
  >
    <span class="mrt-timeline__node" />
  </span>
</template>

<style scoped>
.mrt-timeline__node-col {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: var(--mrt-tl-node);
  height: 100%;
  min-height: inherit;
  justify-self: center;
}

/* Per-row segments replaced by one measured line on MrtTimeline (J22). */
.mrt-timeline__node-col::before {
  display: none;
}

.mrt-timeline__node {
  position: relative;
  z-index: 1;
  flex-shrink: 0;
  width: var(--mrt-tl-node);
  height: var(--mrt-tl-node);
  border: 0.28rem solid var(--mrt-timeline-line, var(--mrt-wizard-text, #151515));
  border-radius: 50%;
  background: var(--mrt-wizard-surface, #ffffff);
  box-sizing: border-box;
}
</style>
