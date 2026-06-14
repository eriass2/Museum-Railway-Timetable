<script setup lang="ts">
import { computed, ref } from 'vue';
import MrtExpandTrigger from './MrtExpandTrigger.vue';
import type { TimeLabelCaParts } from '../../shared/parseTimeLabel';
import { parseTimeLabelCaPrefix } from '../../shared/parseTimeLabel';
import { stopShowsOnRequestInfo, ON_REQUEST_INFO_MARK } from '../../shared/stopTimeFootnotes';
import type { TimelineItem } from './timelineItems';
import {
  buildTimelineItems,
  type MrtTimelineStop,
} from './timelineItems';

type StopDisplayItem = Extract<TimelineItem, { kind: 'stop' }> & {
  timeParts: TimeLabelCaParts;
};

type DisplayTimelineItem = StopDisplayItem | Extract<TimelineItem, { kind: 'toggle' }>;

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

const displayItems = computed((): DisplayTimelineItem[] =>
  timelineItems.value.map((item) => {
    if (item.kind !== 'stop') {
      return item;
    }
    return {
      ...item,
      timeParts: parseTimeLabelCaPrefix(props.formatTime(item.stop)),
    };
  }),
);

function showsInfoForStop(stop: MrtTimelineStop): boolean {
  return stopShowsOnRequestInfo(stop);
}
</script>

<template>
  <div class="mrt-timeline">
    <template v-for="item in displayItems" :key="item.kind === 'stop' ? item.key : 'toggle'">
      <div
        v-if="item.kind === 'stop'"
        class="mrt-timeline__row"
        :class="{ 'is-terminal': item.terminal, 'mrt-timeline__row--cancelled': cancelled }"
      >
        <time class="mrt-timeline__time">
          <span class="mrt-timeline__time-stack">
            <span v-if="item.timeParts.ca" class="mrt-timeline__time-ca">Ca</span>
            <span class="mrt-timeline__time-value">{{ item.timeParts.value }}</span>
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
          align="start"
          full-width
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
  container-type: inline-size;
  container-name: mrt-timeline;
  display: grid;
  gap: 0;
  --mrt-tl-line: 0.35rem;
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

@container mrt-timeline (max-width: 28rem) {
  .mrt-timeline__row {
    min-height: 2.15rem;
  }

  .mrt-timeline__node {
    border-width: 0.22rem;
  }
}
</style>
