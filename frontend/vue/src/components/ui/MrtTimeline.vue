<script setup lang="ts">
import { computed, ref } from 'vue';
import MrtExpandTrigger from './MrtExpandTrigger.vue';
import { footnoteMarksForStop, type FootnoteMark } from '../../shared/stopTimeFootnotes';
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

function marksForStop(stop: MrtTimelineStop): FootnoteMark[] {
  return footnoteMarksForStop(stop);
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
        <time class="mrt-timeline__time">{{ formatTime(item.stop) }}</time>
        <span class="mrt-timeline__node" aria-hidden="true" />
        <span class="mrt-timeline__station">
          {{ item.stop.station_title }}
          <sup
            v-for="mark in marksForStop(item.stop)"
            :key="mark"
            class="mrt-timeline__mark"
          >{{ mark }}</sup>
        </span>
      </div>
      <div v-else class="mrt-timeline__toggle">
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
  position: relative;
  display: grid;
  gap: 0;
  --mrt-tl-time: clamp(3.25rem, 8vw, 5.4rem);
  --mrt-tl-node: clamp(1rem, 2.5vw, 1.6rem);
  --mrt-tl-gap: clamp(0.35rem, 1vw, 0.65rem);
  --mrt-tl-content-start: calc(var(--mrt-tl-time) + var(--mrt-tl-gap) + var(--mrt-tl-node) + var(--mrt-tl-gap));
}

.mrt-timeline::before {
  content: "";
  position: absolute;
  left: calc(var(--mrt-tl-time) + var(--mrt-tl-gap) + (var(--mrt-tl-node) / 2) - 0.175rem);
  top: 0.85rem;
  bottom: 0.85rem;
  width: 0.35rem;
  background: #151515;
  pointer-events: none;
}

.mrt-timeline__row,
.mrt-timeline__toggle {
  position: relative;
  z-index: 1;
  display: grid;
  grid-template-columns: var(--mrt-tl-time) var(--mrt-tl-node) minmax(0, 1fr);
  gap: var(--mrt-tl-gap);
  align-items: start;
}

.mrt-timeline__row {
  min-height: 2.5rem;
}

.mrt-timeline__toggle {
  align-items: center;
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

.mrt-timeline__node {
  position: relative;
  justify-self: center;
  width: var(--mrt-tl-node);
  height: var(--mrt-tl-node);
  margin-top: 0.2rem;
  border: 0.28rem solid #151515;
  border-radius: 50%;
  background: #ffffff;
  box-sizing: border-box;
}

.mrt-timeline__node::after {
  display: none;
}

.mrt-timeline__station {
  font-size: clamp(1rem, 2.4vw, 1.2rem);
  font-weight: 700;
  line-height: 1.25;
  overflow-wrap: anywhere;
  min-width: 0;
}

.mrt-timeline__mark {
  margin-left: 0.12rem;
  font-size: 0.72em;
  font-weight: 700;
  line-height: 0;
  vertical-align: super;
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
