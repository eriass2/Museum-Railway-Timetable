<script setup lang="ts">
import { toRef, watch } from 'vue';
import { adminConfig } from '../types';
import { adminStr } from '../utils/adminLabels';
import { useStopTimes } from '../composables/useStopTimes';
import AdminLoadState from './AdminLoadState.vue';
import { AdminFormActions, AdminStatusMessage, MrtButton } from './ui';
import StopTimePaCheckbox from './StopTimePaCheckbox.vue';
import StopTimePaHeading from './StopTimePaHeading.vue';

const props = defineProps<{ serviceId: number }>();
const cfg = adminConfig();
const serviceId = toRef(props, 'serviceId');
const { stations, loading, error, message, isDirty, load, save } = useStopTimes(serviceId);

defineExpose({ getIsDirty: () => isDirty.value });

watch(serviceId, () => {
  void load();
}, { immediate: true });
</script>

<template>
  <AdminLoadState
    :loading="loading"
    :error="error"
    :loading-text="adminStr(cfg, 'stopTimesLoading')"
    @retry="load"
  >
    <AdminStatusMessage v-if="message" :message="message" />

    <table class="widefat striped mrt-admin-stoptimes">
      <thead>
        <tr>
          <th>{{ adminStr(cfg, 'stopTimesColStops') }}</th>
          <th>{{ adminStr(cfg, 'stopTimesColStation') }}</th>
          <th>{{ adminStr(cfg, 'stopTimesColArrival') }}</th>
          <th>{{ adminStr(cfg, 'stopTimesColDeparture') }}</th>
          <th scope="col"><StopTimePaHeading kind="pickup" /></th>
          <th scope="col"><StopTimePaHeading kind="dropoff" /></th>
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
            <StopTimePaCheckbox
              v-model="row.pickup_allowed"
              kind="pickup"
              :show-label="false"
              :disabled="!cfg.canManage"
            />
          </td>
          <td>
            <StopTimePaCheckbox
              v-model="row.dropoff_allowed"
              kind="dropoff"
              :show-label="false"
              :disabled="!cfg.canManage"
            />
          </td>
        </tr>
      </tbody>
    </table>

    <AdminFormActions v-if="cfg.canManage || cfg.canOperate">
      <MrtButton context="admin" variant="primary" @click="save()">
        {{ adminStr(cfg, 'stopTimesSave') }}
      </MrtButton>
    </AdminFormActions>
  </AdminLoadState>
</template>
