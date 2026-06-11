<script setup lang="ts">
import type { OverviewUiLabels } from '../../shared/overviewUiLabels';
import type { TimetableOverviewIconUrls, TimetableRailGroup } from '../../types/timetableOverview';
import { overviewColumnIsCancelled } from '../../shared/overviewCancelled';
import MrtOverviewRailGroupGrid from './MrtOverviewRailGroupGrid.vue';
import MrtOverviewTimeDisplay from './MrtOverviewTimeDisplay.vue';
import MrtOverviewTransferCell from './MrtOverviewTransferCell.vue';

defineProps<{
  group: TimetableRailGroup;
  iconUrls: TimetableOverviewIconUrls;
  labels: OverviewUiLabels;
}>();
</script>

<template>
  <MrtOverviewRailGroupGrid :group="group" :icon-urls="iconUrls" :labels="labels">
    <template #time-cell="{ row, columnIndex }">
      <MrtOverviewTimeDisplay
        :text="row.cells[columnIndex].text"
        :approximate-time="row.cells[columnIndex].approximateTime"
        :cancelled="overviewColumnIsCancelled(group.columns[columnIndex])"
      />
    </template>
    <template #transfer-cell="{ row, columnIndex }">
      <MrtOverviewTransferCell :row="row" :column-index="columnIndex" :icon-urls="iconUrls" />
    </template>
  </MrtOverviewRailGroupGrid>
</template>
