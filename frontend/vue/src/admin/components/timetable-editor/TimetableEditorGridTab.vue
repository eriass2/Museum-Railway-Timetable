<script setup lang="ts">
import { computed } from 'vue';
import { AdminPanel } from '../ui';
import EditableTimetableOverview from './EditableTimetableOverview.vue';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import type { TimetableOverviewPayload } from '../../../types/timetableOverview';

const props = defineProps<{
  overview: TimetableOverviewPayload | null;
  loading: boolean;
  canManage: boolean;
  canOperate: boolean;
}>();

const emit = defineEmits<{ refresh: [] }>();

const cfg = adminConfig();
const isReadonly = computed(() => !props.canManage && !props.canOperate);
const showOverview = computed(() => !props.loading && !!props.overview);
</script>

<template>
  <AdminPanel class="mrt-vue-root mrt-admin-grid-tab">
    <p class="description">{{ adminStr(cfg, 'editorGridHint') }}</p>
    <p class="description">{{ adminStr(cfg, 'editorStoptimesPaLegend') }}</p>
    <p class="description">{{ adminStr(cfg, 'stopTimesOnRequestHint') }}</p>
    <p v-if="loading" class="description">{{ adminStr(cfg, 'editorLoading') }}</p>
    <EditableTimetableOverview
      v-else-if="showOverview"
      :data="overview!"
      :readonly="isReadonly"
      @refresh-needed="emit('refresh')"
    />
    <p v-else class="description">{{ adminStr(cfg, 'editorGridEmpty') }}</p>
  </AdminPanel>
</template>
