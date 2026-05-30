<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { getDeviations, listTrainTypes, saveDeviations } from '../api/adminRest';
import type { TimetableDetail, TrainTypeRow } from '../types';
import MobileQuickDeparture from './MobileQuickDeparture.vue';
import MobileCancelTraffic from './MobileCancelTraffic.vue';

const props = defineProps<{
  timetableId: number;
  detail: TimetableDetail;
  canOperate: boolean;
  trafficToday: string | null;
}>();

const emit = defineEmits<{ saved: [message: string] }>();

const deviationRows = ref<
  { service_id: number; date: string; trip_label: string; train_type_id: number; notice: string }[]
>([]);
const trainTypes = ref<TrainTypeRow[]>([]);
const loading = ref(true);
const error = ref('');
const cancelMsg = ref('');

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
      row.notice.toLowerCase().includes('inställd'),
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
  void reloadDeviations();
}

async function reloadDeviations() {
  const deviations = await getDeviations(props.timetableId);
  deviationRows.value = deviations.rows;
}

onMounted(async () => {
  try {
    const [deviations, types] = await Promise.all([
      getDeviations(props.timetableId),
      listTrainTypes(),
    ]);
    deviationRows.value = deviations.rows;
    trainTypes.value = types.items;
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Fel';
  } finally {
    loading.value = false;
  }
});

async function saveDeviationChanges() {
  const byService: Record<number, Record<string, { train_type?: number; notice?: string }>> = {};
  for (const row of deviationRows.value) {
    if (!byService[row.service_id]) byService[row.service_id] = {};
    byService[row.service_id][row.date] = {
      train_type: row.train_type_id || undefined,
      notice: row.notice || undefined,
    };
  }
  await saveDeviations(props.timetableId, byService);
  emit('saved', 'Avvikelser sparade');
}
</script>

<template>
  <div class="mrt-admin-mobile-panel">
    <p v-if="loading" class="description">Laddar...</p>
    <p v-else-if="error" class="notice notice-error">{{ error }}</p>

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
      @error="error = $event"
    />
    <p v-if="cancelMsg" class="notice notice-success">{{ cancelMsg }}</p>

    <div class="mrt-admin-mobile-deviations">
      <h3>Avvikelser</h3>
      <div
        v-for="(row, idx) in deviationRows"
        :key="idx"
        class="mrt-admin-mobile-deviation-card"
      >
        <p class="mrt-admin-mobile-deviation-meta">
          <strong>{{ row.date }}</strong> · {{ row.trip_label }}
        </p>
        <p>
          <label>Tågtyp</label>
          <select v-model.number="row.train_type_id" class="widefat" :disabled="!canOperate">
            <option :value="0">— Standard —</option>
            <option v-for="t in trainTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>
        </p>
        <p>
          <label>Meddelande</label>
          <input v-model="row.notice" type="text" class="widefat" :disabled="!canOperate" />
        </p>
      </div>
      <p v-if="canOperate && deviationRows.length">
        <button type="button" class="button button-primary widefat" @click="saveDeviationChanges">
          Spara avvikelser
        </button>
      </p>
      <p v-else-if="!deviationRows.length" class="description">Inga avvikelser för denna tidtabell.</p>
    </div>
  </div>
</template>
