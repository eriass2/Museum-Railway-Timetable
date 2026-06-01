<script setup lang="ts">
import { computed, ref } from 'vue';
import MrtExpandTrigger from './MrtExpandTrigger.vue';

export type MrtTimelineStop = {
  station_title?: string;
  departure_time?: string;
  arrival_time?: string;
};

const props = withDefaults(
  defineProps<{
    stops: MrtTimelineStop[];
    formatTime: (stop: MrtTimelineStop) => string;
    showStopsLabel: string;
    hideStopsLabel: string;
    startExpanded?: boolean;
  }>(),
  { startExpanded: false },
);

const showAllStops = ref(Boolean(props.startExpanded));

const visibleStops = computed(() => {
  if (showAllStops.value || props.stops.length <= 2) {
    return props.stops;
  }
  return [props.stops[0], props.stops[props.stops.length - 1]];
});

const expandLabel = computed(() =>
  showAllStops.value ? props.hideStopsLabel : props.showStopsLabel,
);
</script>

<template>
  <div class="mrt-timeline">
    <div
      v-for="(stop, i) in visibleStops"
      :key="i"
      class="mrt-timeline__row"
      :class="{ 'is-terminal': i === 0 || i === visibleStops.length - 1 }"
    >
      <time class="mrt-timeline__time">{{ formatTime(stop) }}</time>
      <span class="mrt-timeline__node" aria-hidden="true" />
      <span class="mrt-timeline__station">{{ stop.station_title }}</span>
    </div>
    <MrtExpandTrigger
      v-if="stops.length > 2"
      variant="link"
      :expanded="showAllStops"
      :label="expandLabel"
      @toggle="showAllStops = !showAllStops"
    />
  </div>
</template>
