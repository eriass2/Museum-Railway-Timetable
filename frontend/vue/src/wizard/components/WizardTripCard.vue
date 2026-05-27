<script setup lang="ts">
import { computed, ref } from 'vue';
import type { MrtVueConfig } from '../../useMrtConfig';
import type { JourneyConnection } from '../types';
import type { WizardCfg } from '../utils/wizardLabels';
import { cfgStr } from '../utils/wizardLabels';
import {
  arrivalAtDestination,
  connectionLegs,
  departureFromOrigin,
  isTransfer,
} from '../utils/connection';
import { formatDuration, formatTripClock, isWarningNotice } from '../utils/format';
import { legVehicleKind, legVehicleLabel, trainIconUrl } from '../utils/vehicle';
import WizardTripDetail from './WizardTripDetail.vue';

const props = defineProps<{
  config: MrtVueConfig;
  cfg: WizardCfg;
  connection: JourneyConnection;
  legCtx: 'outbound' | 'return';
  legFrom: number;
  legTo: number;
  routeText: string;
}>();

const emit = defineEmits<{ select: [] }>();

const expanded = ref(false);
const detailRef = ref<InstanceType<typeof WizardTripDetail> | null>(null);

const dep = computed(() => formatTripClock(departureFromOrigin(props.connection)));
const arr = computed(() => formatTripClock(arrivalAtDestination(props.connection)));
const meta = computed(() =>
  isTransfer(props.connection)
    ? cfgStr(props.cfg, 'transferTrip', 'Byte')
    : cfgStr(props.cfg, 'directTrip', 'Direktresa'),
);

const legs = computed(() => connectionLegs(props.connection));

function vehicleKind(leg: (typeof legs.value)[0]): string {
  return legVehicleKind(leg, props.cfg);
}

async function toggleDetail(): Promise<void> {
  expanded.value = !expanded.value;
  if (expanded.value) {
    await detailRef.value?.ensureLoaded();
  }
}
</script>

<template>
  <article class="mrt-jw-card mrt-jw-card--trip mrt-journey-wizard__trip-card" :class="{ 'is-expanded': expanded }">
    <div class="mrt-jw-trip-head mrt-journey-wizard__trip-head">
      <div class="mrt-jw-trip-head__copy mrt-journey-wizard__trip-copy">
        <p class="mrt-jw-typo mrt-jw-typo--time mrt-journey-wizard__trip-time">
          <span>{{ dep }}</span> – <span>{{ arr }}</span>
        </p>
        <p class="mrt-jw-typo mrt-jw-typo--route mrt-journey-wizard__trip-route">{{ routeText }}</p>
        <p
          v-if="connection.notice"
          class="mrt-jw-notice mrt-journey-wizard__notice"
          :class="{ 'mrt-jw-notice--warn': isWarningNotice(connection.notice || '') }"
        >
          {{ connection.notice }}
        </p>
      </div>
      <div class="mrt-jw-trip-head__side mrt-journey-wizard__trip-side">
        <div class="mrt-jw-vehicle-row mrt-journey-wizard__vehicle-row">
          <span
            v-for="(leg, li) in legs"
            :key="li"
            class="mrt-jw-vehicle mrt-journey-wizard__vehicle"
            :class="`mrt-jw-vehicle--${vehicleKind(leg)} mrt-journey-wizard__vehicle--${vehicleKind(leg)}`"
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
            <span v-else class="mrt-jw-vehicle__mark mrt-journey-wizard__vehicle-mark" aria-hidden="true" />
            <span>{{ legVehicleLabel(leg) }}</span>
          </span>
        </div>
        <p v-if="connection.duration_minutes" class="mrt-jw-typo mrt-jw-typo--duration mrt-journey-wizard__duration">
          {{ formatDuration(connection.duration_minutes, cfg) }}
        </p>
        <button
          type="button"
          class="mrt-jw-btn mrt-jw-btn--select mrt-journey-wizard__btn-select"
          @click="emit('select')"
        >
          {{ cfgStr(cfg, 'selectTrip', 'Välj →') }}
        </button>
      </div>
    </div>
    <button
      type="button"
      class="mrt-jw-expand mrt-journey-wizard__expand"
      :aria-expanded="expanded"
      @click="toggleDetail"
    >
      <span class="mrt-jw-expand__chevron mrt-journey-wizard__expand-chevron" aria-hidden="true" />
      {{ meta }}
    </button>
    <WizardTripDetail
      v-show="expanded"
      ref="detailRef"
      :config="config"
      :cfg="cfg"
      :connection="connection"
      :leg-from="legFrom"
      :leg-to="legTo"
      :expanded="false"
    />
  </article>
</template>
