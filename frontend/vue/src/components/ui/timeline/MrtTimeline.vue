<script setup lang="ts">
import { computed } from 'vue';
import { buildTimelineItems } from './buildTimelineItems';
import { mapTimelineDisplay } from './mapTimelineDisplay';
import MrtTimelineStopRow from './MrtTimelineStopRow.vue';
import MrtTimelineToggleRow from './MrtTimelineToggleRow.vue';
import { useTimelineExpand } from './useTimelineExpand';
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
</script>

<template>
  <ol
    class="mrt-timeline"
    :aria-label="listLabel || undefined"
  >
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
  display: grid;
  gap: 0;
  list-style: none;
  margin: 0;
  padding: 0;
  --mrt-tl-line: 0.35rem;
  --mrt-timeline-line: var(--mrt-wizard-text, #151515);
}

@container mrt-timeline (max-width: 28rem) {
  :deep(.mrt-timeline__row) {
    min-height: 2.15rem;
  }

  :deep(.mrt-timeline__node) {
    border-width: 0.22rem;
  }
}
</style>
