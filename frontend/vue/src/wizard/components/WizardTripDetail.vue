<script setup lang="ts">
import { computed, ref } from 'vue';
import { mrtPost } from '../../api/mrtApi';
import type { MrtVueConfig } from '../../useMrtConfig';
import type { ConnectionDetailPayload, JourneyConnection, JourneyLeg, TimelineStop } from '../types';
import type { WizardCfg } from '../utils/wizardLabels';
import { cfgStr } from '../utils/wizardLabels';
import { formatTripClock, formatDuration } from '../utils/format';
import { connectionLegs } from '../utils/connection';
import { legVehicleKind, legVehicleLabel, trainIconUrl } from '../utils/vehicle';

const props = defineProps<{
  config: MrtVueConfig;
  cfg: WizardCfg;
  connection: JourneyConnection;
  legFrom: number;
  legTo: number;
  expanded: boolean;
}>();

const loading = ref(false);
const error = ref('');
const stops = ref<TimelineStop[]>([]);
const notice = ref('');
const showAllStops = ref(props.expanded);
const loaded = ref(false);

const legs = computed(() => connectionLegs(props.connection));

function stationTime(s: TimelineStop): string {
  return formatTripClock(s.departure_time || s.arrival_time || '');
}

const visibleStops = computed(() => {
  const list = stops.value;
  if (showAllStops.value || list.length <= 2) {
    return list;
  }
  return [list[0], list[list.length - 1]];
});

async function loadDetail(): Promise<void> {
  if (loaded.value) {
    return;
  }
  loading.value = true;
  error.value = '';
  const leg = legs.value[0];
  const res = await mrtPost<ConnectionDetailPayload>(props.config, 'mrt_journey_connection_detail', {
    from_station: props.legFrom,
    to_station: props.legTo,
    service_id: props.connection.service_id,
  });
  loading.value = false;
  if (!res.success || !res.data) {
    error.value = cfgStr(props.cfg, 'errorGeneric', 'Error');
    return;
  }
  stops.value = res.data.detail?.stops || [];
  notice.value = res.data.notice || '';
  loaded.value = true;
}

async function ensureLoaded(): Promise<void> {
  if (!loaded.value) {
    await loadDetail();
  }
}

defineExpose({ ensureLoaded });
</script>

<template>
  <div class="mrt-jw-card__detail mrt-journey-wizard__detail" :class="{ 'is-passed-expanded': showAllStops }">
    <p v-if="loading" class="mrt-empty">{{ cfgStr(cfg, 'loading', 'Loading...') }}</p>
    <p v-else-if="error" class="mrt-alert mrt-alert-error">{{ error }}</p>
    <template v-else-if="loaded">
      <p v-if="notice" class="mrt-jw-notice mrt-journey-wizard__notice">
        <strong>{{ cfgStr(cfg, 'noticeLabel', 'Notice') }}:</strong> {{ notice }}
      </p>
      <div class="mrt-jw-timeline mrt-journey-wizard__timeline">
        <div
          v-for="(stop, i) in visibleStops"
          :key="i"
          class="mrt-jw-timeline__row mrt-journey-wizard__timeline-row"
          :class="{ 'is-terminal': i === 0 || i === visibleStops.length - 1 }"
        >
          <time class="mrt-jw-timeline__time mrt-journey-wizard__timeline-time">{{ stationTime(stop) }}</time>
          <span class="mrt-jw-timeline__node mrt-journey-wizard__timeline-node" aria-hidden="true" />
          <span class="mrt-jw-timeline__station mrt-journey-wizard__timeline-station">{{ stop.station_title }}</span>
        </div>
        <button
          v-if="stops.length > 2"
          type="button"
          class="mrt-jw-btn mrt-jw-btn--passed mrt-journey-wizard__passed-toggle"
          @click="showAllStops = !showAllStops"
        >
          {{ showAllStops ? '∧ ' + cfgStr(cfg, 'hideStops', 'Hide') : '∨ ' + cfgStr(cfg, 'showStops', 'Show stops') }}
        </button>
      </div>
    </template>
  </div>
</template>
