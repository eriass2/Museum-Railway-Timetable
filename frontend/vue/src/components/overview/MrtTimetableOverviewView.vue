<script setup lang="ts">
import type { OverviewUiLabels } from '../../shared/overviewUiLabels';
import type { TimetableOverviewPayload } from '../../types/timetableOverview';
import MrtOverviewBranchGroup from './MrtOverviewBranchGroup.vue';
import MrtOverviewPrintKey from './MrtOverviewPrintKey.vue';
import MrtOverviewRailGroup from './MrtOverviewRailGroup.vue';
import MrtTimetableOverviewShell from './MrtTimetableOverviewShell.vue';

defineProps<{
  data: TimetableOverviewPayload;
  labels: OverviewUiLabels;
}>();
</script>

<template>
  <MrtTimetableOverviewShell :data="data">
    <template #group="{ group, iconUrls }">
      <MrtOverviewRailGroup
        v-if="group.kind === 'rail'"
        :group="group"
        :icon-urls="iconUrls"
        :labels="labels"
      />
      <MrtOverviewBranchGroup v-else :group="group" :icon-urls="iconUrls" :labels="labels" />
    </template>
    <template #footer="{ printKey }">
      <MrtOverviewPrintKey :rows="printKey" :labels="labels" />
    </template>
  </MrtTimetableOverviewShell>
</template>
