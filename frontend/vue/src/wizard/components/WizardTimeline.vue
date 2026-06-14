<script setup lang="ts">
import MrtTimeline from '../../components/ui/MrtTimeline.vue';
import { formatTimelineStopTime } from '../../shared/formatTimelineStopTime';
import type { TimelineStop } from '../types';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import { cfgStr } from '../utils/wizardLabels';

defineProps<{
  cfg: WizardCfg;
  stops: TimelineStop[];
  startExpanded?: boolean;
  cancelled?: boolean;
}>();
</script>

<template>
  <MrtTimeline
    :stops="stops"
    :format-time="formatTimelineStopTime"
    :list-label="cfgStr(cfg, 'timelineListLabel', 'Resans hållplatser')"
    :show-stops-label="cfgStr(cfg, 'showStops', 'Visa passerade stationer')"
    :hide-stops-label="cfgStr(cfg, 'hideStops', 'Dölj passerade stationer')"
    :start-expanded="startExpanded"
    :cancelled="cancelled"
  />
</template>
