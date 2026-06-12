<script setup lang="ts">
import { computed, toRef, watch } from 'vue';
import { adminConfig } from '../../types';
import { adminStr } from '../../utils/adminLabels';
import { useStopTimes } from '../../composables/timetable-editor/useStopTimes';
import { AdminFormActions, MrtAlert, MrtAsyncState, MrtButton } from '../ui';
import StopTimePaHeading from './StopTimePaHeading.vue';
import StopTimeTableRow from './StopTimeTableRow.vue';

const props = defineProps<{ serviceId: number }>();
const cfg = adminConfig();
const serviceId = toRef(props, 'serviceId');
const { stations, loading, error, message, isDirty, load, save } = useStopTimes(serviceId);

const canEditTimes = computed(() => cfg.canManage || cfg.canOperate);
const canEditModes = computed(() => cfg.canManage);

defineExpose({ getIsDirty: () => isDirty.value });

watch(serviceId, () => {
  void load();
}, { immediate: true });
</script>

<template>
  <MrtAsyncState
    context="admin"
    :loading="loading"
    :error="error"
    :loading-text="adminStr(cfg, 'stopTimesLoading')"
    :retry-label="adminStr(cfg, 'retry', 'Försök igen')"
    @retry="load"
  >
    <MrtAlert v-if="message" context="admin" variant="success">{{ message }}</MrtAlert>
    <p class="description">{{ adminStr(cfg, 'editorStoptimesPaLegend') }}</p>
    <p class="description">{{ adminStr(cfg, 'stopTimesOnRequestHint') }}</p>

    <table class="widefat striped mrt-admin-stoptimes">
      <thead>
        <tr>
          <th>{{ adminStr(cfg, 'stopTimesColStops') }}</th>
          <th>{{ adminStr(cfg, 'stopTimesColStation') }}</th>
          <th>{{ adminStr(cfg, 'stopTimesColArrival') }}</th>
          <th>{{ adminStr(cfg, 'stopTimesColDeparture') }}</th>
          <th scope="col"><StopTimePaHeading kind="pickup" /></th>
          <th scope="col"><StopTimePaHeading kind="dropoff" /></th>
          <th>{{ adminStr(cfg, 'stopTimesColApproximate') }}</th>
        </tr>
      </thead>
      <tbody>
        <StopTimeTableRow
          v-for="row in stations"
          :key="row.id"
          :row="row"
          :can-edit-times="canEditTimes"
          :can-edit-modes="canEditModes"
        />
      </tbody>
    </table>

    <AdminFormActions v-if="canEditTimes">
      <MrtButton context="admin" variant="primary" @click="save()">
        {{ adminStr(cfg, 'stopTimesSaveButton') }}
      </MrtButton>
    </AdminFormActions>
  </MrtAsyncState>
</template>

<style scoped>
.mrt-admin-stoptimes :deep(input[type='time']) {
  max-width: 8em;
}
</style>
