<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import MrtAccentButton from '../../components/ui/MrtAccentButton.vue';
import MrtExpandTrigger from '../../components/ui/MrtExpandTrigger.vue';
import MrtTripCard from '../../components/ui/MrtTripCard.vue';
import MrtTripSummary from '../../components/ui/MrtTripSummary.vue';
import MrtVehicleRow from '../../components/ui/MrtVehicleRow.vue';
import { useWizardContext } from '../../composables/useWizardContext';
import type { JourneyConnection } from '../types';
import { useConnectionLegDisplay } from '../composables/useConnectionLegDisplay';
import { cfgStr } from '../utils/wizardLabels';
import {
  connectionDoorToDoorMinutes,
  connectionLegs,
  connectionTransferCount,
  formatTransferTripLabel,
  isTransfer,
} from '../utils/connection';
import { formatDuration, connectionNoticeIsCancelled, isWarningNotice } from '../utils/format';
import { legsToVehicleItems } from '../utils/vehicle';
import WizardTripDetail from './WizardTripDetail.vue';

const props = defineProps<{
  connection: JourneyConnection;
  legCtx: 'outbound' | 'return';
}>();

const { cfg } = useWizardContext();
const emit = defineEmits<{ select: [] }>();

const expanded = ref(false);
const detailRef = ref<InstanceType<typeof WizardTripDetail> | null>(null);

const { routeText, timeRange } = useConnectionLegDisplay(
  () => props.connection,
  props.legCtx,
);

const meta = computed(() => {
  if (!isTransfer(props.connection)) {
    return cfgStr(cfg, 'directTrip', 'Direktresa');
  }
  return formatTransferTripLabel(connectionTransferCount(props.connection), cfg.value);
});

const legs = computed(() => connectionLegs(props.connection));

const doorToDoorMinutes = computed(() => connectionDoorToDoorMinutes(props.connection));

const vehicleItems = computed(() => legsToVehicleItems(legs.value, cfg.value));

const isCancelled = computed(() => connectionNoticeIsCancelled(props.connection));

async function toggleDetail(): Promise<void> {
  expanded.value = !expanded.value;
  if (expanded.value) {
    await nextTick();
    await detailRef.value?.ensureLoaded();
  }
}
</script>

<template>
  <MrtTripCard :expanded="expanded">
    <template #copy>
      <MrtTripSummary
        :time-range="timeRange"
        :route="routeText"
        :notice="connection.notice"
        :notice-warn="isWarningNotice(connection.notice || '')"
        :notice-cancelled="isCancelled"
      />
    </template>
    <template #side>
      <MrtVehicleRow :items="vehicleItems" />
        <p v-if="doorToDoorMinutes !== null" class="mrt-trip-card__duration">
        {{ formatDuration(doorToDoorMinutes, cfg) }}
      </p>
      <MrtAccentButton
        variant="select"
        type="button"
        :disabled="isCancelled"
        @click="emit('select')"
      >
        {{ cfgStr(cfg, 'selectTrip', 'Välj →') }}
      </MrtAccentButton>
    </template>
    <template #actions>
      <MrtExpandTrigger :expanded="expanded" :label="meta" @toggle="toggleDetail" />
    </template>
    <WizardTripDetail
      v-if="expanded"
      ref="detailRef"
      :connection="connection"
      :leg-ctx="legCtx"
    />
  </MrtTripCard>
</template>
