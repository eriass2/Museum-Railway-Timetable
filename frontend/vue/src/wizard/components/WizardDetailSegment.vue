<script setup lang="ts">
import { computed, unref, type MaybeRef } from 'vue';
import MrtDetailSegment from '../../components/ui/MrtDetailSegment.vue';
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
      label: legVehicleLabel(props.segment.leg, cfgRef.value),
      iconUrl: trainIconUrl(kind, cfgRef.value),
    },
  ];
});
</script>

<template>
  <MrtDetailSegment
    :title="segment.title"
    :notice="segment.notice"
    :notice-label="cfgStr(cfg, 'noticeLabel', 'Trafikmeddelande')"
    :transfer-text="showTransfer ? transferText : undefined"
  >
    <template v-if="segment.leg" #meta>
      <span v-if="segment.leg.duration_minutes" class="mrt-detail-segment__duration">
        {{ formatDuration(segment.leg.duration_minutes, cfgRef) }}
      </span>
      <MrtVehicleRow :items="vehicleItems" />
    </template>
    <WizardTimeline :cfg="cfgRef" :stops="segment.stops" :start-expanded="false" />
  </MrtDetailSegment>
</template>
