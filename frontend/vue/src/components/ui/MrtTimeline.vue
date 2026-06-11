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
