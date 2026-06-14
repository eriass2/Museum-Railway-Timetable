<script setup lang="ts">
import { computed, ref } from 'vue';
import MrtExpandTrigger from './MrtExpandTrigger.vue';
import { stopShowsOnRequestInfo, ON_REQUEST_INFO_MARK } from '../../shared/stopTimeFootnotes';
import {
  buildTimelineItems,
  type MrtTimelineStop,
} from './timelineItems';

export type { MrtTimelineStop };

const props = withDefaults(
  defineProps<{
    stops: MrtTimelineStop[];
    formatTime: (stop: MrtTimelineStop) => string;
    showStopsLabel: string;
    hideStopsLabel: string;
    startExpanded?: boolean;
    cancelled?: boolean;
  }>(),
  { startExpanded: false, cancelled: false },
);

const showAllStops = ref(Boolean(props.startExpanded));

const expandLabel = computed(() =>
  showAllStops.value ? props.hideStopsLabel : props.showStopsLabel,
);

const timelineItems = computed(() =>
  buildTimelineItems(props.stops, showAllStops.value),
);

function showsInfoForStop(stop: MrtTimelineStop): boolean {
  return stopShowsOnRequestInfo(stop);
}

/** Split "Ca 10.00" for stacked layout (J25). */
function timeParts(stop: MrtTimelineStop): { ca: boolean; value: string } {
  const label = props.formatTime(stop).trim();
  if (label.startsWith('Ca ')) {
    return { ca: true, value: label.slice(3) };
  }
  return { ca: false, value: label };
}
</script>

<template>
  <div class="mrt-timeline">
    <template v-for="item in timelineItems" :key="item.kind === 'stop' ? item.key : 'toggle'">
      <div
        v-if="item.kind === 'stop'"
        class="mrt-timeline__row"
        :class="{ 'is-terminal': item.terminal, 'mrt-timeline__row--cancelled': cancelled }"
      >
        <time class="mrt-timeline__time">
          <span class="mrt-timeline__time-stack">
            <span v-if="timeParts(item.stop).ca" class="mrt-timeline__time-ca">Ca</span>
            <span class="mrt-timeline__time-value">{{ timeParts(item.stop).value }}</span>
          </span>
        </time>
        <span class="mrt-timeline__node-col" aria-hidden="true">
          <span class="mrt-timeline__node" />
        </span>
        <span class="mrt-timeline__station">
          {{ item.stop.station_title }}
          <span
            v-if="showsInfoForStop(item.stop)"
            class="mrt-timeline__info"
            :aria-label="ON_REQUEST_INFO_MARK"
            role="img"
          >{{ ON_REQUEST_INFO_MARK }}</span>
        </span>
      </div>
      <div v-else class="mrt-timeline__toggle">
        <span class="mrt-timeline__time-spacer" aria-hidden="true" />
        <span class="mrt-timeline__node-col mrt-timeline__node-col--empty" aria-hidden="true" />
        <MrtExpandTrigger
          variant="link"
          :expanded="showAllStops"
          :label="expandLabel"
          @toggle="showAllStops = !showAllStops"
        />
      </div>
    </template>
  </div>
</template>

<style scoped>
.mrt-timeline {
  display: grid;
  gap: 0;
  --mrt-tl-time: clamp(3.25rem, 8vw, 5.4rem);
  --mrt-tl-node: clamp(1rem, 2.5vw, 1.6rem);
  --mrt-tl-gap: clamp(0.35rem, 1vw, 0.65rem);
  --mrt-tl-line: 0.35rem;
  --mrt-tl-content-start: calc(var(--mrt-tl-time) + var(--mrt-tl-gap) + var(--mrt-tl-node) + var(--mrt-tl-gap));
}

.mrt-timeline__row,
.mrt-timeline__toggle {
  display: grid;
  grid-template-columns: var(--mrt-tl-time) var(--mrt-tl-node) minmax(0, 1fr);
  gap: var(--mrt-tl-gap);
  align-items: center;
}

.mrt-timeline__row {
  min-height: 2.5rem;
}

.mrt-timeline__toggle {
  padding: 0.15rem 0;
}

.mrt-timeline__toggle :deep(.mrt-expand-trigger--link) {
  grid-column: 3;
  margin: 0;
  max-width: 100%;
  width: 100%;
  justify-content: flex-start;
  overflow-wrap: anywhere;
}

.mrt-timeline__time {
  text-align: right;
  font-size: clamp(0.95rem, 2.2vw, 1.15rem);
  font-weight: 700;
}

.mrt-timeline__time-stack {
  display: inline-flex;
  flex-direction: column;
  align-items: flex-end;
  line-height: 1.15;
  gap: 0.05rem;
}

.mrt-timeline__time-ca {
  font-size: 0.72em;
  font-weight: 600;
  letter-spacing: 0.02em;
}

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
  top: 0;
  bottom: 0;
  width: var(--mrt-tl-line);
  transform: translateX(-50%);
  background: #151515;
  pointer-events: none;
}

.mrt-timeline__node {
  position: relative;
  z-index: 1;
  flex-shrink: 0;
  width: var(--mrt-tl-node);
  height: var(--mrt-tl-node);
  border: 0.28rem solid #151515;
  border-radius: 50%;
  background: #ffffff;
  box-sizing: border-box;
}

.mrt-timeline__station {
  font-size: clamp(1rem, 2.4vw, 1.2rem);
  font-weight: 700;
  line-height: 1.25;
  overflow-wrap: anywhere;
  min-width: 0;
}

.mrt-timeline__info {
  margin-left: 0.2rem;
  font-size: 0.92em;
  line-height: 1;
  vertical-align: middle;
}

.mrt-timeline__row:not(.is-terminal) .mrt-timeline__station,
.mrt-timeline__row:not(.is-terminal) .mrt-timeline__time {
  font-weight: 500;
}

.mrt-timeline__row--cancelled .mrt-timeline__time,
.mrt-timeline__row--cancelled .mrt-timeline__station {
  text-decoration: line-through;
  color: var(--mrt-text-error, #b32d2e);
}
</style>
