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

.mrt-timeline__node-col::before {
  content: "";
  position: absolute;
  left: 50%;
  width: var(--mrt-tl-line, 0.35rem);
  transform: translateX(-50%);
  background: var(--mrt-timeline-line, var(--mrt-wizard-text, #151515));
  pointer-events: none;
}

.mrt-timeline__node-col--segment-down::before {
  top: 50%;
  bottom: -1px;
}

.mrt-timeline__node-col--segment-up::before {
  top: -1px;
  bottom: 50%;
}

.mrt-timeline__node-col--segment-through::before {
  top: -1px;
  bottom: -1px;
}

.mrt-timeline__node-col--segment-none::before {
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
