<script setup lang="ts">
import { computed, ref } from 'vue';
import type { TimelineStop } from '../types';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import { cfgStr } from '../utils/wizardLabels';
import { formatTripClock } from '../utils/format';

const props = defineProps<{
  cfg: WizardCfg;
  stops: TimelineStop[];
  startExpanded?: boolean;
}>();

const showAllStops = ref(Boolean(props.startExpanded));

const visibleStops = computed(() => {
  if (showAllStops.value || props.stops.length <= 2) {
    return props.stops;
  }
  return [props.stops[0], props.stops[props.stops.length - 1]];
});

function stationTime(s: TimelineStop): string {
  return formatTripClock(s.departure_time || s.arrival_time || '');
}
</script>

<template>
  <div class="mrt-journey-wizard__timeline">
    <div
      v-for="(stop, i) in visibleStops"
      :key="i"
      class="mrt-journey-wizard__timeline-row"
      :class="{ 'is-terminal': i === 0 || i === visibleStops.length - 1 }"
    >
      <time class="mrt-journey-wizard__timeline-time">{{ stationTime(stop) }}</time>
      <span class="mrt-journey-wizard__timeline-node" aria-hidden="true" />
      <span class="mrt-journey-wizard__timeline-station">{{ stop.station_title }}</span>
    </div>
    <button
      v-if="stops.length > 2"
      type="button"
      class="mrt-journey-wizard__passed-toggle"
      @click="showAllStops = !showAllStops"
    >
      {{ showAllStops ? '∧ ' + cfgStr(cfg, 'hideStops', 'Dölj') : '∨ ' + cfgStr(cfg, 'showStops', 'Visa hållplatser') }}
    </button>
  </div>
</template>
