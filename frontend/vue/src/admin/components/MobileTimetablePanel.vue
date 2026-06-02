<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { getDeviations, listTrainTypes, saveDeviations } from '../api/adminRest';
import type { TimetableDetail, TrainTypeRow } from '../types';
import { adminConfig } from '../types';
import { adminErrorMessage, adminStr } from '../utils/adminLabels';
import { deviationsToSavePayload, type DeviationRow } from '../utils/deviationsPayload';
import { useAdminResource } from '../composables/useAdminResource';
import AdminLoadState from './AdminLoadState.vue';
import MobileQuickDeparture from './MobileQuickDeparture.vue';
import MobileCancelTraffic from './MobileCancelTraffic.vue';
import { AdminDeviationRowFields, AdminStatusMessage, MrtButton } from './ui';

const props = defineProps<{
  timetableId: number;
  detail: TimetableDetail;
  canOperate: boolean;
  trafficToday: string | null;
}>();

const emit = defineEmits<{ saved: [message: string] }>();

const cfg = adminConfig();
const deviationRows = ref<DeviationRow[]>([]);
const trainTypes = ref<TrainTypeRow[]>([]);
const cancelMsg = ref('');

const { loading, error, load, reload, data } = useAdminResource({
  fetch: async () => {
    const [deviations, types] = await Promise.all([
      getDeviations(props.timetableId),
      listTrainTypes(),
    ]);
    return { rows: deviations.rows, trainTypes: types.items };
  },
  errorMessage: (e) => adminErrorMessage(cfg, e, 'genericError'),
});

watch(
  data,
  (payload) => {
    if (!payload) {
      return;
    }
    deviationRows.value = payload.rows.map((row) => ({ ...row }));
    trainTypes.value = payload.trainTypes;
  },
  { immediate: true },
);

const cancelledNotice = computed(() => adminStr(cfg, 'trafficCancelledNotice').toLowerCase());

const showCancelToday = computed(
  () =>
    props.trafficToday !== null &&
    props.detail.dates.includes(props.trafficToday),
);

const trafficTodayPayload = computed(() => {
  if (!props.trafficToday) return null;
  const today = props.trafficToday;
  const cancelled = deviationRows.value.filter(
    (row) =>
      row.date === today &&
      row.notice.toLowerCase().includes(cancelledNotice.value),
  ).length;
  const total = props.detail.services.length;
  return {
    date: today,
    timetable_id: props.timetableId,
    timetable_title: props.detail.title,
    services_count: total,
    cancelled_count: cancelled,
    all_cancelled: total > 0 && cancelled >= total,
  };
});

function onCancelDone(message: string) {
  cancelMsg.value = message;
  emit('saved', message);
  void reload();
}

function onCancelError(message: string) {
  error.value = message;
}

async function saveDeviationChanges() {
  await saveDeviations(props.timetableId, deviationsToSavePayload(deviationRows.value));
  emit('saved', adminStr(cfg, 'editorSavedDeviations'));
}
</script>

<template>
  <div class="mrt-admin-mobile-panel">
    <AdminLoadState
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'mobileLoading')"
      @retry="load"
    >
      <MobileQuickDeparture
        :services="detail.services"
        :can-edit="canOperate"
        @saved="emit('saved', $event)"
      />

      <MobileCancelTraffic
        v-if="showCancelToday && trafficTodayPayload"
        :traffic="trafficTodayPayload"
        :can-operate="canOperate"
        @done="onCancelDone"
        @error="onCancelError"
      />
      <AdminStatusMessage v-if="cancelMsg" :message="cancelMsg" />

      <div class="mrt-admin-mobile-deviations">
        <h3>{{ adminStr(cfg, 'mobileDeviationsTitle') }}</h3>
        <div
          v-for="(row, idx) in deviationRows"
          :key="idx"
          class="mrt-admin-mobile-deviation-card"
        >
          <AdminDeviationRowFields
            :train-type-id="row.train_type_id"
            :notice="row.notice"
            :meta="`${row.date} · ${row.trip_label}`"
            :train-types="trainTypes"
            :can-operate="canOperate"
            @update:train-type-id="row.train_type_id = $event"
            @update:notice="row.notice = $event"
          />
        </div>
        <p v-if="canOperate && deviationRows.length">
          <MrtButton context="admin" variant="primary" wide @click="saveDeviationChanges">
            {{ adminStr(cfg, 'editorSaveDeviations') }}
          </MrtButton>
        </p>
        <p v-else-if="!deviationRows.length" class="description">
          {{ adminStr(cfg, 'mobileNoDeviations') }}
        </p>
      </div>
    </AdminLoadState>
  </div>
</template>
