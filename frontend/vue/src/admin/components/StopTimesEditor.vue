<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { getStopTimes, saveStopTimes } from '../api/adminRest';
import type { StopTimeRow } from '../types';
import { adminConfig } from '../types';
import { adminStr } from '../utils/adminLabels';
import AdminFormActions from './ui/AdminFormActions.vue';

const props = defineProps<{ serviceId: number }>();
const cfg = adminConfig();
const stations = ref<StopTimeRow[]>([]);
const loading = ref(true);
const error = ref('');
const message = ref('');

async function load() {
  loading.value = true;
  error.value = '';
  try {
    const res = await getStopTimes(props.serviceId);
    stations.value = res.stations;
  } catch (e) {
    error.value = e instanceof Error ? e.message : adminStr(cfg, 'genericError');
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  void load();
});

async function save(explicit = true) {
  if (!cfg.canManage && !cfg.canOperate) return;
  const stops = stations.value.map((s) => ({
    station_id: s.id,
    stops_here: s.stops_here ? '1' : '0',
    arrival: s.arrival_time || '',
    departure: s.departure_time || '',
    pickup: s.pickup_allowed ? '1' : '',
    dropoff: s.dropoff_allowed ? '1' : '',
  }));
  try {
    const res = await saveStopTimes(props.serviceId, stops, !explicit);
    stations.value = res.stations;
    message.value = adminStr(cfg, 'stopTimesSaved');
  } catch (e) {
    error.value = e instanceof Error ? e.message : adminStr(cfg, 'saveFailed');
  }
}
</script>

<template>
  <div>
    <p v-if="loading" class="description">{{ adminStr(cfg, 'stopTimesLoading') }}</p>
    <p v-if="error" class="notice notice-error">{{ error }}</p>
    <p v-if="message" class="notice notice-success">{{ message }}</p>

    <table v-if="!loading" class="widefat striped mrt-admin-stoptimes">
      <thead>
        <tr>
          <th>{{ adminStr(cfg, 'stopTimesColStops') }}</th>
          <th>{{ adminStr(cfg, 'stopTimesColStation') }}</th>
          <th>{{ adminStr(cfg, 'stopTimesColArrival') }}</th>
          <th>{{ adminStr(cfg, 'stopTimesColDeparture') }}</th>
          <th>{{ adminStr(cfg, 'stopTimesColPickup') }}</th>
          <th>{{ adminStr(cfg, 'stopTimesColDropoff') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in stations" :key="row.id">
          <td>
            <input v-model="row.stops_here" type="checkbox" :disabled="!cfg.canManage && !cfg.canOperate" />
          </td>
          <td>{{ row.name }}</td>
          <td>
            <input
              v-model="row.arrival_time"
              type="time"
              class="mrt-input"
              :disabled="!cfg.canManage && !cfg.canOperate"
            />
          </td>
          <td>
            <input
              v-model="row.departure_time"
              type="time"
              class="mrt-input"
              :disabled="!cfg.canManage && !cfg.canOperate"
            />
          </td>
          <td>
            <input v-model="row.pickup_allowed" type="checkbox" :disabled="!cfg.canManage" />
          </td>
          <td>
            <input v-model="row.dropoff_allowed" type="checkbox" :disabled="!cfg.canManage" />
          </td>
        </tr>
      </tbody>
    </table>
    <AdminFormActions v-if="cfg.canManage || cfg.canOperate">
      <button type="button" class="button button-primary" @click="save(true)">
        {{ adminStr(cfg, 'stopTimesSaveButton') }}
      </button>
    </AdminFormActions>
  </div>
</template>
