<script setup lang="ts">
import type { OverviewUiLabels } from '../../shared/overviewUiLabels';
import type { TimetableOverviewPayload } from '../../types/timetableOverview';
import MrtOverviewBranchGroup from './MrtOverviewBranchGroup.vue';
import MrtOverviewPrintKey from './MrtOverviewPrintKey.vue';
import MrtOverviewRailGroup from './MrtOverviewRailGroup.vue';
import '../../styles/timetable-overview.css';

defineProps<{
  data: TimetableOverviewPayload;
  labels: OverviewUiLabels;
}>();
</script>

<template>
  <div class="mrt-ov" role="region" :aria-label="data.title">
    <p v-if="data.scope !== 'day' && data.typeBanner?.label" class="mrt-ov-banner">
      {{ data.typeBanner.label }}
    </p>
    <h2 v-else-if="data.scope === 'day'" class="mrt-ov-day-title">{{ data.title }}</h2>

    <template v-for="(group, gi) in data.groups" :key="gi">
      <MrtOverviewRailGroup
        v-if="group.kind === 'rail'"
        :group="group"
        :icon-urls="data.iconUrls"
        :labels="labels"
      />
      <MrtOverviewBranchGroup v-else :group="group" :labels="labels" />
      <div v-if="gi < data.groups.length - 1" class="mrt-ov-separator" aria-hidden="true" />
    </template>

    <MrtOverviewPrintKey :rows="data.printKey" :labels="labels" />
  </div>
</template>
