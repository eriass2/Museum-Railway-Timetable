<script setup lang="ts">
import { AdminPanel } from './ui';
import StopTimesEditor from './StopTimesEditor.vue';
import EditableTimetableOverview from './EditableTimetableOverview.vue';
import { adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';
import type { TimetableDetail } from '../types';
import type { TimetableOverviewPayload } from '../../types/timetableOverview';

defineProps<{
  detail: TimetableDetail;
  overview: TimetableOverviewPayload | null;
  gridOverviewLoading: boolean;
  canManage: boolean;
  canOperate: boolean;
}>();

const selectedServiceId = defineModel<number>('selectedServiceId', { required: true });

const emit = defineEmits<{
  gridToggle: [event: Event];
  refreshOverview: [];
}>();

const cfg = adminConfig();
</script>

<template>
  <AdminPanel class="mrt-vue-root">
    <p class="description">{{ adminStr(cfg, 'editorStoptimesHint') }}</p>
    <p class="mrt-admin-stoptimes-trip-picker">
      <label for="mrt-stoptimes-service">{{ adminStr(cfg, 'editorStoptimesTripLabel') }}</label>
      <select id="mrt-stoptimes-service" v-model.number="selectedServiceId">
        <option :value="0">{{ adminStr(cfg, 'editorSelectTrip') }}</option>
        <option v-for="s in detail.services" :key="s.id" :value="s.id">
          {{ s.service_number }} — {{ s.destination || s.route_name }}
        </option>
      </select>
    </p>
    <StopTimesEditor v-if="selectedServiceId" :service-id="selectedServiceId" />
    <details class="mrt-mt-sm mrt-admin-stoptimes-grid" @toggle="emit('gridToggle', $event)">
      <summary>{{ adminStr(cfg, 'editorStoptimesGridSummary') }}</summary>
      <p class="description">{{ adminStr(cfg, 'editorStoptimesGridHint') }}</p>
      <p v-if="gridOverviewLoading" class="description">{{ adminStr(cfg, 'editorLoading') }}</p>
      <EditableTimetableOverview
        v-else-if="overview"
        :data="overview"
        :readonly="!canManage && !canOperate"
        @refresh-needed="emit('refreshOverview')"
      />
    </details>
  </AdminPanel>
</template>
