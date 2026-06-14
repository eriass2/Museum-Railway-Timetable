<script setup lang="ts">
import { computed } from 'vue';
import { ON_REQUEST_INFO_ARIA_LABEL } from '../../../shared/stopTimeFootnotes';
import MrtInfoMark from '../MrtInfoMark.vue';
import type { StopDisplayRow } from './mapTimelineDisplay';
import {
  isTerminalPosition,
  lineSegmentForPosition,
} from './timelinePresentation';
import MrtTimelineNode from './MrtTimelineNode.vue';
import MrtTimelineTime from './MrtTimelineTime.vue';

const props = defineProps<{
  row: StopDisplayRow;
  cancelled?: boolean;
}>();

const terminal = computed(() => isTerminalPosition(props.row.position));
const lineSegment = computed(() => lineSegmentForPosition(props.row.position));
</script>

<template>
  <li
    class="mrt-timeline__item mrt-timeline__row mrt-timeline-row-grid"
    :class="{ 'mrt-timeline__row--cancelled': cancelled }"
  >
    <MrtTimelineTime
      :time-parts="row.timeParts"
      :terminal="terminal"
    />
    <MrtTimelineNode :segment="lineSegment" />
    <span
      class="mrt-timeline__station"
      :class="{ 'is-terminal': terminal }"
    >
      {{ row.stationTitle }}
      <MrtInfoMark
        v-if="row.showInfo"
        class="mrt-timeline__info"
        :label="ON_REQUEST_INFO_ARIA_LABEL"
      />
    </span>
  </li>
</template>

<style scoped>
.mrt-timeline__row {
  min-height: 2.5rem;
}

.mrt-timeline__station {
  font-size: clamp(1rem, 2.4vw, 1.2rem);
  font-weight: 700;
  line-height: 1.25;
  overflow-wrap: anywhere;
  min-width: 0;
}

.mrt-timeline__station:not(.is-terminal) {
  font-weight: 500;
}

.mrt-timeline__row--cancelled :deep(.mrt-timeline__time),
.mrt-timeline__row--cancelled .mrt-timeline__station {
  text-decoration: line-through;
  color: var(--mrt-text-error, #b32d2e);
}

.mrt-timeline__info {
  margin-left: 0;
}
</style>
