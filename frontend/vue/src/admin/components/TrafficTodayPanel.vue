<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { cancelTrafficToday } from '../api/adminRest';
import { adminConfirm } from '../composables/adminConfirm';
import type { TrafficToday } from '../types';
import { adminFmt, adminFmtN, adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';
import { AdminActionBar, AdminPanel, AdminStatusMessage, MrtButton } from './ui';

const props = defineProps<{
  traffic: TrafficToday;
  canOperate: boolean;
}>();

const cfg = adminConfig();
const router = useRouter();
const busy = ref(false);
const message = ref('');
const error = ref('');
const localAllCancelled = ref(false);

const effectiveTraffic = computed(() => ({
  ...props.traffic,
  all_cancelled: props.traffic.all_cancelled || localAllCancelled.value,
}));

const statusText = computed(() => {
  const traffic = effectiveTraffic.value;
  if (traffic.services_count === 0) {
    return adminStr(cfg, 'trafficTodayNoServices');
  }
  if (traffic.all_cancelled) {
    return adminFmtN(cfg, 'trafficTodayAllCancelled', { 1: traffic.services_count });
  }
  return adminFmtN(cfg, 'trafficTodaySummary', {
    1: traffic.services_count,
    2: traffic.timetable_title,
  });
});

async function cancelAll() {
  if (!props.canOperate || busy.value) return;
  const ok = await adminConfirm({
    title: adminStr(cfg, 'trafficTodayCancelTitle'),
    message: adminFmtN(cfg, 'trafficTodayCancelMessage', {
      1: props.traffic.services_count,
      2: props.traffic.date,
    }),
    confirmLabel: adminStr(cfg, 'trafficTodayCancelButton'),
    danger: true,
  });
  if (!ok) {
    return;
  }
  busy.value = true;
  message.value = '';
  error.value = '';
  try {
    const notice = adminStr(cfg, 'trafficCancelledNotice');
    const res = await cancelTrafficToday(props.traffic.date, notice);
    message.value =
      res.services_updated > 0
        ? adminFmt(cfg, 'trafficTodayCancelSuccess', res.services_updated)
        : adminStr(cfg, 'trafficTodayCancelNone');
    if (res.services_updated > 0) {
      localAllCancelled.value = true;
    }
  } catch (e) {
    error.value = e instanceof Error ? e.message : adminStr(cfg, 'trafficTodayCancelFailed');
  } finally {
    busy.value = false;
  }
}

function openTimetable() {
  void router.push(`/timetables/${props.traffic.timetable_id}`);
}
</script>

<template>
  <AdminPanel class="mrt-admin-ops-today">
    <h2>{{ adminStr(cfg, 'trafficTodayTitle') }}</h2>
    <p class="description">{{ traffic.date }} — {{ statusText }}</p>
    <AdminStatusMessage v-if="message" :message="message" />
    <AdminStatusMessage v-if="error" type="error" :message="error" />
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
      <MrtButton context="admin" variant="secondary" @click="openTimetable">
        {{ adminStr(cfg, 'trafficTodayOpenTimetable') }}
      </MrtButton>
      <MrtButton
        v-if="traffic.services_count > 0"
        context="admin"
        variant="secondary"
        @click="openTimetable"
      >
        {{ adminStr(cfg, 'trafficTodayEditDeviations') }}
      </MrtButton>
    </AdminActionBar>
  </AdminPanel>
</template>
