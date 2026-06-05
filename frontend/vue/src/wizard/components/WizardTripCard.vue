<script setup lang="ts">
import { computed, ref } from 'vue';
import MrtAccentButton from '../../components/ui/MrtAccentButton.vue';
import MrtExpandTrigger from '../../components/ui/MrtExpandTrigger.vue';
import MrtTripCard from '../../components/ui/MrtTripCard.vue';
import MrtTripSummary from '../../components/ui/MrtTripSummary.vue';
import MrtVehicleRow from '../../components/ui/MrtVehicleRow.vue';
import { useWizardContext } from '../../composables/useWizardContext';
import type { JourneyConnection } from '../types';
import { cfgStr } from '../utils/wizardLabels';
import {
  arrivalAtDestination,
  connectionDoorToDoorMinutes,
  connectionLegs,
  connectionTransferCount,
  departureFromOrigin,
  formatTransferTripLabel,
  isTransfer,
} from '../utils/connection';
import { formatDuration, formatTripClock, isWarningNotice } from '../utils/format';
import { legsToVehicleItems } from '../utils/vehicle';
import WizardTripDetail from './WizardTripDetail.vue';

const props = defineProps<{
  connection: JourneyConnection;
  legCtx: 'outbound' | 'return';
}>();

const { store, cfg } = useWizardContext();
const emit = defineEmits<{ select: [] }>();

const expanded = ref(false);
const detailRef = ref<InstanceType<typeof WizardTripDetail> | null>(null);

const routeText = computed(() =>
  props.legCtx === 'return'
    ? `${store.toTitle} → ${store.fromTitle}`
    : `${store.fromTitle} → ${store.toTitle}`,
);

const timeRange = computed(
  () =>
    `${formatTripClock(departureFromOrigin(props.connection))} – ${formatTripClock(arrivalAtDestination(props.connection))}`,
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

async function toggleDetail(): Promise<void> {
  expanded.value = !expanded.value;
  if (expanded.value) {
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
      />
    </template>
    <template #side>
      <MrtVehicleRow :items="vehicleItems" />
        <p v-if="doorToDoorMinutes !== null" class="mrt-trip-card__duration">
        {{ formatDuration(doorToDoorMinutes, cfg) }}
      </p>
      <MrtAccentButton variant="select" type="button" @click="emit('select')">
        {{ cfgStr(cfg, 'selectTrip', 'Välj →') }}
      </MrtAccentButton>
    </template>
    <template #actions>
      <MrtExpandTrigger :expanded="expanded" :label="meta" @toggle="toggleDetail" />
    </template>
    <WizardTripDetail
      v-show="expanded"
      ref="detailRef"
      :connection="connection"
      :leg-ctx="legCtx"
    />
  </MrtTripCard>
</template>
