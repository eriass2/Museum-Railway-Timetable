<script setup lang="ts">
import { computed, ref } from 'vue';
import { useWizardContext } from '../../composables/useWizardContext';
import type { JourneyConnection } from '../types';
import { cfgStr } from '../utils/wizardLabels';
import {
  arrivalAtDestination,
  connectionLegs,
  departureFromOrigin,
  isTransfer,
} from '../utils/connection';
import { formatDuration, formatTripClock, isWarningNotice } from '../utils/format';
import { legVehicleKind, legVehicleLabel, trainIconUrl } from '../utils/vehicle';
import WizardAccentButton from './WizardAccentButton.vue';
import WizardTripDetail from './WizardTripDetail.vue';

const props = defineProps<{
  connection: JourneyConnection;
  legCtx: 'outbound' | 'return';
}>();

const { store, cfg } = useWizardContext();
const emit = defineEmits<{ select: [] }>();

const expanded = ref(false);
const detailRef = ref<InstanceType<typeof WizardTripDetail> | null>(null);

const legFrom = computed(() => (props.legCtx === 'return' ? store.toId : store.fromId));
const legTo = computed(() => (props.legCtx === 'return' ? store.fromId : store.toId));
const routeText = computed(() =>
  props.legCtx === 'return'
    ? `${store.toTitle} → ${store.fromTitle}`
    : `${store.fromTitle} → ${store.toTitle}`,
);

const dep = computed(() => formatTripClock(departureFromOrigin(props.connection)));
const arr = computed(() => formatTripClock(arrivalAtDestination(props.connection)));
const meta = computed(() =>
  isTransfer(props.connection)
    ? cfgStr(cfg, 'transferTrip', 'Byte')
    : cfgStr(cfg, 'directTrip', 'Direktresa'),
);

const legs = computed(() => connectionLegs(props.connection));

function vehicleKind(leg: (typeof legs.value)[0]): string {
  return legVehicleKind(leg, cfg.value);
}

async function toggleDetail(): Promise<void> {
  expanded.value = !expanded.value;
  if (expanded.value) {
    await detailRef.value?.ensureLoaded();
  }
}
</script>

<template>
  <article class="mrt-journey-wizard__trip-card" :class="{ 'is-expanded': expanded }">
    <div class="mrt-journey-wizard__trip-head">
      <div class="mrt-journey-wizard__trip-copy">
        <p class="mrt-journey-wizard__trip-time">
          <span>{{ dep }}</span> – <span>{{ arr }}</span>
        </p>
        <p class="mrt-journey-wizard__trip-route">{{ routeText }}</p>
        <p
          v-if="connection.notice"
          class="mrt-journey-wizard__notice"
          :class="{ 'mrt-journey-wizard__notice--warn': isWarningNotice(connection.notice || '') }"
        >
          {{ connection.notice }}
        </p>
      </div>
      <div class="mrt-journey-wizard__trip-side">
        <div class="mrt-journey-wizard__vehicle-row">
          <span
            v-for="(leg, li) in legs"
            :key="li"
            class="mrt-journey-wizard__vehicle"
            :class="`mrt-journey-wizard__vehicle--${vehicleKind(leg)}`"
          >
            <img
              v-if="trainIconUrl(vehicleKind(leg), cfg)"
              :src="trainIconUrl(vehicleKind(leg), cfg)"
              class="mrt-journey-wizard__vehicle-icon mrt-train-type-icon-img"
              width="48"
              height="24"
              decoding="async"
              alt=""
            >
            <span v-else class="mrt-journey-wizard__vehicle-mark" aria-hidden="true" />
            <span>{{ legVehicleLabel(leg) }}</span>
          </span>
        </div>
        <p v-if="connection.duration_minutes" class="mrt-journey-wizard__duration">
          {{ formatDuration(connection.duration_minutes, cfg) }}
        </p>
        <WizardAccentButton variant="select" type="button" @click="emit('select')">
          {{ cfgStr(cfg, 'selectTrip', 'Välj →') }}
        </WizardAccentButton>
      </div>
    </div>
    <button
      type="button"
      class="mrt-journey-wizard__expand"
      :aria-expanded="expanded"
      @click="toggleDetail"
    >
      <span class="mrt-journey-wizard__expand-chevron" aria-hidden="true" />
      {{ meta }}
    </button>
    <WizardTripDetail
      v-show="expanded"
      ref="detailRef"
      :connection="connection"
      :leg-ctx="legCtx"
    />
  </article>
</template>
