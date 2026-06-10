<script setup lang="ts">
import { computed, ref } from 'vue';
import MrtExpandTrigger from './MrtExpandTrigger.vue';
import { footnoteMarksForStop, type FootnoteMark } from '../../shared/stopTimeFootnotes';

export type MrtTimelineStop = {
  station_title?: string;
  departure_time?: string;
  arrival_time?: string;
  on_request_pickup?: boolean;
  on_request_dropoff?: boolean;
  on_request_both?: boolean;
};

type TimelineItem =
  | { kind: 'stop'; stop: MrtTimelineStop; terminal: boolean; key: string }
  | { kind: 'toggle' };

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

const timelineItems = computed((): TimelineItem[] => {
  const { stops } = props;
  if (stops.length <= 2) {
    return stops.map((stop, index) => ({
      kind: 'stop' as const,
      stop,
      terminal: index === 0 || index === stops.length - 1,
      key: `stop-${index}`,
    }));
  }

  const items: TimelineItem[] = [
    { kind: 'stop', stop: stops[0], terminal: true, key: 'stop-first' },
    { kind: 'toggle' },
  ];

  if (showAllStops.value) {
    stops.slice(1, -1).forEach((stop, index) => {
      items.push({
        kind: 'stop',
        stop,
        terminal: false,
        key: `stop-mid-${index}`,
      });
    });
  }

  items.push({
    kind: 'stop',
    stop: stops[stops.length - 1],
    terminal: true,
    key: 'stop-last',
  });

  return items;
});

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
