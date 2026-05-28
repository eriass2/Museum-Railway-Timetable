<script setup lang="ts">
import { computed, unref, type MaybeRef } from 'vue';
import MrtHeading from '../../components/ui/MrtHeading.vue';
import MrtVehicleRow from '../../components/ui/MrtVehicleRow.vue';
import type { MrtVehicleItem } from '../../components/ui/types';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import type { LegSegment } from '../composables/useConnectionDetail';
import { cfgStr } from '../utils/wizardLabels';
import { formatDuration } from '../utils/format';
import { legVehicleKind, legVehicleLabel, trainIconUrl } from '../utils/vehicle';
import WizardTimeline from './WizardTimeline.vue';

const props = defineProps<{
  cfg: MaybeRef<WizardCfg>;
  segment: LegSegment;
  showTransfer: boolean;
  transferText: string;
}>();

const cfgRef = computed(() => unref(props.cfg));

const vehicleItems = computed((): MrtVehicleItem[] => {
  if (!props.segment.leg) {
    return [];
  }
  const kind = legVehicleKind(props.segment.leg, cfgRef.value);
  return [
    {
      kind,
      label: legVehicleLabel(props.segment.leg),
      iconUrl: trainIconUrl(kind, cfgRef.value),
    },
  ];
});
</script>

<template>
  <div class="mrt-journey-wizard__detail-segment mrt-mb-sm">
    <MrtHeading
      v-if="segment.title"
      level="h4"
      size="md"
      class="mrt-journey-wizard__detail-title"
    >
      {{ segment.title }}
    </MrtHeading>
    <p v-if="segment.notice" class="mrt-journey-wizard__notice">
      <strong>{{ cfgStr(cfg, 'noticeLabel', 'Notis') }}:</strong> {{ segment.notice }}
    </p>
    <div v-if="segment.leg" class="mrt-journey-wizard__timeline-leg">
      <span v-if="segment.leg.duration_minutes" class="mrt-journey-wizard__leg-duration">
        {{ formatDuration(segment.leg.duration_minutes, cfgRef) }}
      </span>
      <MrtVehicleRow :items="vehicleItems" />
    </div>
    <WizardTimeline :cfg="cfgRef" :stops="segment.stops" :start-expanded="false" />
    <div v-if="showTransfer" class="mrt-journey-wizard__transfer-block">
      {{ transferText }}
    </div>
  </div>
</template>
