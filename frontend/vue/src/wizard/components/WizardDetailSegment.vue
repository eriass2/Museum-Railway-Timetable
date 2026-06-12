<script setup lang="ts">
import { computed, unref, type MaybeRef } from 'vue';
import MrtDetailSegment from '../../components/ui/MrtDetailSegment.vue';
import MrtVehicleRow from '../../components/ui/MrtVehicleRow.vue';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import type { LegSegment } from '../composables/useConnectionDetail';
import { cfgStr } from '../utils/wizardLabels';
import { formatDuration } from '../utils/format';
import { legToVehicleItem } from '../utils/vehicle';
import WizardTimeline from './WizardTimeline.vue';

const props = defineProps<{
  cfg: MaybeRef<WizardCfg>;
  segment: LegSegment;
  showTransfer: boolean;
  transferText: string;
}>();

const cfgRef = computed(() => unref(props.cfg));

const segmentCancelled = computed(() => props.segment.isCancelled === true);

const vehicleItems = computed(() =>
  props.segment.leg ? [legToVehicleItem(props.segment.leg, cfgRef.value)] : [],
);
</script>

<template>
  <MrtDetailSegment
    :title="segment.title"
    :notice="segment.notice"
    :notice-label="cfgStr(cfg, 'noticeLabel', 'Trafikmeddelande')"
    :notice-cancelled="segmentCancelled"
    :transfer-text="showTransfer ? transferText : undefined"
  >
    <template v-if="segment.leg" #meta>
      <span
        v-if="segment.leg.duration_minutes"
        class="mrt-detail-segment__duration"
        :class="{ 'mrt-detail-segment__duration--cancelled': segmentCancelled }"
      >
        {{ formatDuration(segment.leg.duration_minutes, cfgRef) }}
      </span>
      <MrtVehicleRow :items="vehicleItems" />
    </template>
    <WizardTimeline
      :cfg="cfgRef"
      :stops="segment.stops"
      :start-expanded="false"
      :cancelled="segmentCancelled"
    />
  </MrtDetailSegment>
</template>

<style scoped>
.mrt-detail-segment__duration {
  font-size: 1.05rem;
  font-weight: 900;
  color: #3f3f3f;
}

.mrt-detail-segment__duration--cancelled {
  text-decoration: line-through;
  color: var(--mrt-text-error, #b32d2e);
}
</style>
