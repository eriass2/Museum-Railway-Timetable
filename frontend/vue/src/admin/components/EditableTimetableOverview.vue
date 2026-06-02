<script setup lang="ts">
import { timetableTypeOverviewClass } from '../../shared/calendarDay';
import { overviewUiLabels } from '../../shared/overviewUiLabels';
import type { TimetableOverviewPayload } from '../../types/timetableOverview';
import MrtOverviewBranchGroup from '../../components/overview/MrtOverviewBranchGroup.vue';
import MrtOverviewPrintKey from '../../components/overview/MrtOverviewPrintKey.vue';
import EditableOverviewRailGroup from './EditableOverviewRailGroup.vue';
import { useOverviewGridEdit } from '../composables/useOverviewGridEdit';
import '../../styles/timetable-overview.css';

defineProps<{
  data: TimetableOverviewPayload;
  readonly?: boolean;
}>();

const editor = useOverviewGridEdit();
const labels = overviewUiLabels({});
</script>

<template>
  <div
    class="mrt-ov"
    :class="timetableTypeOverviewClass(data.timetableType)"
    role="region"
    :aria-label="data.title"
  >
    <p v-if="editor.error.value" class="notice notice-error">{{ editor.error.value }}</p>
    <p v-if="editor.message.value" class="notice notice-success">{{ editor.message.value }}</p>

    <p v-if="data.typeBanner?.label" class="mrt-ov-banner">
      {{ data.typeBanner.label }}
    </p>

    <template v-for="(group, gi) in data.groups" :key="gi">
      <EditableOverviewRailGroup
        v-if="group.kind === 'rail'"
        :group="group"
        :icon-urls="data.iconUrls"
        :editor="editor"
        :readonly="readonly"
      />
      <MrtOverviewBranchGroup v-else :group="group" :icon-urls="data.iconUrls" :labels="labels" />
      <div v-if="gi < data.groups.length - 1" class="mrt-ov-separator" aria-hidden="true" />
    </template>

    <MrtOverviewPrintKey :rows="data.printKey" :labels="labels" />
  </div>
</template>
