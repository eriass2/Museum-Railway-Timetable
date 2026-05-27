<script setup lang="ts">
import { computed, ref } from 'vue';
import { mrtPost } from '../../api/mrtApi';
import type { MrtVueConfig } from '../../useMrtConfig';
import type { ConnectionDetailPayload, JourneyConnection, JourneyLeg, TimelineStop } from '../types';
import type { WizardCfg } from '../utils/wizardLabels';
import { cfgStr } from '../utils/wizardLabels';
import { formatDuration } from '../utils/format';
import { connectionLegs } from '../utils/connection';
import { legVehicleKind, legVehicleLabel, trainIconUrl } from '../utils/vehicle';
import WizardTimeline from './WizardTimeline.vue';

type LegSegment = {
  title: string;
  stops: TimelineStop[];
  notice: string;
  leg?: JourneyLeg;
};

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
const segments = ref<LegSegment[]>([]);
const loaded = ref(false);

const legs = computed(() => connectionLegs(props.connection));
const isMulti = computed(() => legs.value.length > 1);
const legTpl = computed(() => cfgStr(props.cfg, 'legSegmentLabel', 'Delsträcka %d'));

async function loadLegDetail(leg: JourneyLeg): Promise<ConnectionDetailPayload | null> {
  const from = leg.from_station_id || props.legFrom;
  const to = leg.to_station_id || props.legTo;
  const res = await mrtPost<ConnectionDetailPayload>(props.config, 'mrt_journey_connection_detail', {
    from_station: from,
    to_station: to,
    service_id: leg.service_id,
  });
  return res.success && res.data ? res.data : null;
}

async function loadDetail(): Promise<void> {
  if (loaded.value) {
    return;
  }
  loading.value = true;
  error.value = '';
  segments.value = [];

  if (isMulti.value) {
    for (let i = 0; i < legs.value.length; i++) {
      const leg = legs.value[i];
      const data = await loadLegDetail(leg);
      if (!data) {
        error.value = cfgStr(props.cfg, 'errorGeneric', 'Error');
        loading.value = false;
        return;
      }
      segments.value.push({
        title: legTpl.value.replace('%d', String(i + 1)),
        stops: data.detail?.stops || [],
        notice: data.notice || '',
        leg,
      });
    }
  } else {
    const data = await loadLegDetail(legs.value[0]);
    if (!data) {
      error.value = cfgStr(props.cfg, 'errorGeneric', 'Error');
      loading.value = false;
      return;
    }
    segments.value.push({
      title: '',
      stops: data.detail?.stops || [],
      notice: data.notice || '',
      leg: legs.value[0],
    });
  }

  loading.value = false;
  loaded.value = true;
}

function transferLabel(index: number): string {
  const wait = props.connection.transfer_wait_minutes;
  if (wait !== null && wait !== undefined && !Number.isNaN(Number(wait))) {
    return cfgStr(props.cfg, 'transferWait', '%d min byte').replace('%d', String(wait));
  }
  return cfgStr(props.cfg, 'transferTrip', 'Byte');
}

async function ensureLoaded(): Promise<void> {
  if (!loaded.value) {
    await loadDetail();
  }
}

defineExpose({ ensureLoaded });
</script>

<template>
  <div
    class="mrt-jw-card__detail mrt-journey-wizard__detail"
    :class="{ 'mrt-journey-wizard__detail--multi': isMulti, 'is-passed-expanded': expanded }"
  >
    <p v-if="loading" class="mrt-empty">{{ cfgStr(cfg, 'loading', 'Loading...') }}</p>
    <p v-else-if="error" class="mrt-alert mrt-alert-error">{{ error }}</p>
    <template v-else-if="loaded">
      <div v-for="(seg, si) in segments" :key="si" class="mrt-journey-wizard__detail-segment mrt-mb-sm">
        <h4 v-if="seg.title" class="mrt-journey-wizard__detail-title">{{ seg.title }}</h4>
        <p v-if="seg.notice" class="mrt-jw-notice mrt-journey-wizard__notice">
          <strong>{{ cfgStr(cfg, 'noticeLabel', 'Notice') }}:</strong> {{ seg.notice }}
        </p>
        <div v-if="seg.leg" class="mrt-jw-timeline__leg mrt-journey-wizard__timeline-leg">
          <span v-if="seg.leg.duration_minutes" class="mrt-jw-timeline__leg-duration">
            {{ formatDuration(seg.leg.duration_minutes, cfg) }}
          </span>
          <span
            class="mrt-jw-vehicle mrt-journey-wizard__vehicle"
            :class="`mrt-jw-vehicle--${legVehicleKind(seg.leg, cfg)}`"
          >
            <img
              v-if="trainIconUrl(legVehicleKind(seg.leg, cfg), cfg)"
              :src="trainIconUrl(legVehicleKind(seg.leg, cfg), cfg)"
              class="mrt-journey-wizard__vehicle-icon"
              width="48"
              height="24"
              alt=""
            >
            <span>{{ legVehicleLabel(seg.leg) }}</span>
          </span>
        </div>
        <WizardTimeline :cfg="cfg" :stops="seg.stops" :start-expanded="expanded" />
        <div
          v-if="isMulti && si < segments.length - 1"
          class="mrt-jw-timeline__transfer mrt-journey-wizard__transfer-block"
        >
          {{ transferLabel(si) }}
        </div>
      </div>
    </template>
  </div>
</template>
