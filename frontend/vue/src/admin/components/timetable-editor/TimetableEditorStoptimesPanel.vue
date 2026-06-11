<script setup lang="ts">
import { computed, toRef } from 'vue';
import {
  AdminBackNav,
  AdminPanel,
  AdminRowActions,
  MrtButton,
} from '../ui';
import StopTimesEditor from './StopTimesEditor.vue';
import { useStoptimesPanelBack } from '../../composables/timetable-editor/useStoptimesPanelBack';
import { formatServiceTripLabel } from '../../utils/timetable-editor/serviceTripLabel';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import type { TimetableDetail } from '../../types';
export type StoptimesPanelView = 'list' | 'detail';

const props = defineProps<{
  detail: TimetableDetail;
  canManage: boolean;
  canOperate: boolean;
  viewMode: StoptimesPanelView;
}>();

const selectedServiceId = defineModel<number>('selectedServiceId', { required: true });

const emit = defineEmits<{
  back: [];
  openDetail: [serviceId: number];
}>();

const cfg = adminConfig();
const viewModeRef = toRef(props, 'viewMode');
const { stopTimesRef, tryLeaveDetail, onBackClick } = useStoptimesPanelBack(viewModeRef, () => {
  emit('back');
});

defineExpose({ requestBackToList: tryLeaveDetail });

const selectedTripLabel = computed(() => {
  const service = props.detail.services.find((row) => row.id === selectedServiceId.value);
  return formatServiceTripLabel(service, adminStr(cfg, 'editorSelectTrip'));
});
</script>

<template>
  <AdminPanel class="mrt-vue-root">
    <template v-if="viewMode === 'list'">
      <p class="description">{{ adminStr(cfg, 'editorStoptimesHint') }}</p>
      <table class="widefat striped">
        <thead>
          <tr>
            <th>{{ adminStr(cfg, 'editorColTrip') }}</th>
            <th>{{ adminStr(cfg, 'editorColRoute') }}</th>
            <th>{{ adminStr(cfg, 'editorColDestination') }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in detail.services" :key="s.id">
            <td>{{ s.service_number }}</td>
            <td>{{ s.route_name }}</td>
            <td>{{ s.destination || '—' }}</td>
            <td>
              <AdminRowActions>
                <MrtButton
                  context="admin"
                  variant="secondary"
                  @click="emit('openDetail', s.id)"
                >
                  {{ adminStr(cfg, 'editorStopptimes') }}
                </MrtButton>
              </AdminRowActions>
            </td>
          </tr>
        </tbody>
      </table>
    </template>

    <template v-else>
      <AdminBackNav @back="onBackClick" />
      <p class="description mrt-admin-stoptimes-trip-label">{{ selectedTripLabel }}</p>
      <StopTimesEditor
        v-if="selectedServiceId"
        ref="stopTimesRef"
        :service-id="selectedServiceId"
      />
    </template>
  </AdminPanel>
</template>

<style scoped>
.mrt-admin-stoptimes-trip-label {
  font-weight: 600;
  margin-bottom: 12px;
}
</style>
