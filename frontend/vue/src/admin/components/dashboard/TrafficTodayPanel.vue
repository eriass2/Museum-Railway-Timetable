<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import {
  mobileCancelResultLabels,
  trafficTodayCancelResultLabels,
  useCancelTrafficToday,
} from '../../composables/useCancelTrafficToday';
import type { TrafficToday } from '../../types';
import { adminFmtN, adminStr } from '../../utils/adminLabels';
import { trafficTodayStatusText } from '../../utils/dashboard/trafficTodayStatus';
import { adminConfig } from '../../types';
import { AdminActionBar, AdminPanel, MrtAlert, MrtButton } from '../ui';

const props = defineProps<{
  traffic: TrafficToday;
  canOperate: boolean;
}>();

const cfg = adminConfig();
const router = useRouter();
const message = ref('');
const error = ref('');
const localAllCancelled = ref(false);

const effectiveTraffic = computed(() => ({
  ...props.traffic,
  all_cancelled: props.traffic.all_cancelled || localAllCancelled.value,
}));

const statusText = computed(() =>
  trafficTodayStatusText(cfg, effectiveTraffic.value),
);

const resultLabels = trafficTodayCancelResultLabels(cfg);

const { busy, cancelAll: runCancelAll } = useCancelTrafficToday(
  cfg,
  () => props.traffic,
  () => props.canOperate,
  () => ({
    title: adminStr(cfg, 'trafficTodayCancelTitle'),
    message: adminFmtN(cfg, 'trafficTodayCancelMessage', {
      1: props.traffic.services_count,
      2: props.traffic.date,
    }),
    confirmLabel: adminStr(cfg, 'trafficTodayCancelButton'),
  }),
  () => resultLabels,
);

async function cancelAll() {
  message.value = '';
  error.value = '';
  try {
    const result = await runCancelAll();
    if (result === null) {
      return;
    }
    message.value = result;
    if (result !== resultLabels.none) {
      localAllCancelled.value = true;
    }
  } catch (e) {
    error.value = e instanceof Error ? e.message : adminStr(cfg, 'trafficTodayCancelFailed');
  }
}

function openTimetable(tab?: string) {
  void router.push({
    path: `/timetables/${props.traffic.timetable_id}`,
    query: tab ? { tab } : undefined,
  });
}
</script>

<template>
  <AdminPanel class="mrt-admin-ops-today">
    <h2>{{ adminStr(cfg, 'trafficTodayTitle') }}</h2>
    <p class="description">{{ traffic.date }} — {{ statusText }}</p>
    <MrtAlert v-if="message" context="admin" variant="success">{{ message }}</MrtAlert>
    <MrtAlert v-if="error" context="admin" variant="error">{{ error }}</MrtAlert>
    <AdminActionBar>
      <MrtButton
        v-if="canOperate && !effectiveTraffic.all_cancelled && effectiveTraffic.services_count > 0"
        context="admin"
        variant="primary"
        :disabled="busy"
        @click="cancelAll"
      >
        {{ adminStr(cfg, 'trafficTodayCancelTitle') }}
      </MrtButton>
      <MrtButton context="admin" variant="secondary" @click="openTimetable()">
        {{ adminStr(cfg, 'trafficTodayOpenTimetable') }}
      </MrtButton>
      <MrtButton
        v-if="traffic.services_count > 0"
        context="admin"
        variant="secondary"
        @click="openTimetable('deviations')"
      >
        {{ adminStr(cfg, 'trafficTodayEditDeviations') }}
      </MrtButton>
    </AdminActionBar>
  </AdminPanel>
</template>
