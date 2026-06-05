<script setup lang="ts">
import { overviewUiLabels } from '../../shared/overviewUiLabels';
import type { TimetableOverviewPayload } from '../../types/timetableOverview';
import MrtOverviewBranchGroup from '../../components/overview/MrtOverviewBranchGroup.vue';
import MrtOverviewPrintKey from '../../components/overview/MrtOverviewPrintKey.vue';
import MrtTimetableOverviewShell from '../../components/overview/MrtTimetableOverviewShell.vue';
import { AdminStatusMessage } from './ui';
import EditableOverviewRailGroup from './EditableOverviewRailGroup.vue';
import { useOverviewGridEdit } from '../composables/useOverviewGridEdit';

defineProps<{
  data: TimetableOverviewPayload;
  readonly?: boolean;
}>();

const emit = defineEmits<{ 'refresh-needed': [] }>();

const editor = useOverviewGridEdit();
const labels = overviewUiLabels({});

function onGridCellSaved(): void {
  editor.clearCache();
  emit('refresh-needed');
}
</script>

<template>
  <MrtTimetableOverviewShell :data="data" :show-day-title="false">
    <template #prepend>
      <AdminStatusMessage v-if="editor.error.value" type="error" :message="editor.error.value" />
      <AdminStatusMessage v-if="editor.message.value" :message="editor.message.value" />
    </template>
    <template #group="{ group, iconUrls }">
      <EditableOverviewRailGroup
        v-if="group.kind === 'rail'"
        :group="group"
        :icon-urls="iconUrls"
        :editor="editor"
        :readonly="readonly"
        @saved="onGridCellSaved"
      />
      <MrtOverviewBranchGroup v-else :group="group" :icon-urls="iconUrls" :labels="labels" />
    </template>
    <template #footer="{ printKey }">
      <MrtOverviewPrintKey :rows="printKey" :labels="labels" />
    </template>
  </MrtTimetableOverviewShell>
</template>
