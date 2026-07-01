<script setup lang="ts">
import { computed, ref } from 'vue';
import { buildTimelineItems } from './buildTimelineItems';
import { mapTimelineDisplay } from './mapTimelineDisplay';
import MrtTimelineStopRow from './MrtTimelineStopRow.vue';
import MrtTimelineToggleRow from './MrtTimelineToggleRow.vue';
import { useTimelineExpand } from './useTimelineExpand';
import { useTimelineRouteLine } from './useTimelineRouteLine';
import type { MrtTimelineStop } from './types';
import './timeline-row-grid.css';

export type { MrtTimelineStop };

const props = withDefaults(
  defineProps<{
    stops: MrtTimelineStop[];
    formatTime: (stop: MrtTimelineStop) => string;
    showStopsLabel: string;
    hideStopsLabel: string;
    listLabel?: string;
    startExpanded?: boolean;
    cancelled?: boolean;
  }>(),
  { startExpanded: false, cancelled: false },
);

const { showAllStops, toggleStops } = useTimelineExpand(Boolean(props.startExpanded));

const expandLabel = computed(() =>
  showAllStops.value ? props.hideStopsLabel : props.showStopsLabel,
);

const displayItems = computed(() =>
  mapTimelineDisplay(
    buildTimelineItems(props.stops, showAllStops.value),
    props.formatTime,
  ),
);

const olRef = ref<HTMLElement | null>(null);
const { routeLineStyle } = useTimelineRouteLine(olRef, displayItems);
const showRouteLine = computed(() => props.stops.length >= 2);
</script>

<template>
  <ol
    ref="olRef"
    class="mrt-timeline"
    :class="{ 'mrt-timeline--routed': showRouteLine }"
    :aria-label="listLabel || undefined"
  >
    <span
      v-if="showRouteLine"
      class="mrt-timeline__route-line"
      aria-hidden="true"
      :style="routeLineStyle"
    />
    <template v-for="item in displayItems" :key="item.kind === 'stop' ? item.key : 'toggle'">
      <MrtTimelineStopRow
        v-if="item.kind === 'stop'"
        :row="item"
        :cancelled="cancelled"
      />
      <MrtTimelineToggleRow
        v-else
        :expanded="showAllStops"
        :label="expandLabel"
        @toggle="toggleStops"
      />
    </template>
  </ol>
</template>

<style scoped>
.mrt-timeline {
  container-type: inline-size;
  container-name: mrt-timeline;
  position: relative;
  display: grid;
  gap: 0;
  list-style: none;
  margin: 0;
  padding: 0;
  max-width: var(--mrt-max-narrow);
  --mrt-tl-line: 0.35rem;
  --mrt-timeline-line: var(--mrt-wizard-text, #151515);
}

.mrt-timeline--routed :deep(.mrt-timeline__item) {
  margin: 0;
  padding: 0;
  position: relative;
  z-index: 1;
}

.mrt-timeline__route-line {
  position: absolute;
  left: calc(var(--mrt-tl-time) + var(--mrt-tl-gap) + (var(--mrt-tl-node) / 2));
  transform: translateX(-50%);
  width: var(--mrt-tl-line);
  background: var(--mrt-timeline-line);
  pointer-events: none;
  z-index: 0;
}

@container mrt-timeline (max-width: var(--mrt-max-narrow)) {
  :deep(.mrt-timeline__row) {
    min-height: 2.15rem;
  }

  :deep(.mrt-timeline__node) {
    border-width: 0.22rem;
  }
}
</style>
