<script setup lang="ts">
import MrtTimeline from '../../components/ui/MrtTimeline.vue';
import type { TimelineStop } from '../types';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import { cfgStr } from '../utils/wizardLabels';
import { formatTripClock } from '../utils/format';

defineProps<{
  cfg: WizardCfg;
  stops: TimelineStop[];
  startExpanded?: boolean;
}>();

function stationTime(s: TimelineStop): string {
  return formatTripClock(s.departure_time || s.arrival_time || '');
}
</script>

<template>
  <MrtTimeline
    :stops="stops"
    :format-time="stationTime"
    :show-stops-label="cfgStr(cfg, 'showStops', 'Visa passerade stationer')"
    :hide-stops-label="cfgStr(cfg, 'hideStops', 'Dölj passerade stationer')"
    :start-expanded="startExpanded"
  />
</template>
