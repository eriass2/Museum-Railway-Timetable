<script setup lang="ts">
import type { OverviewUiLabels } from '../../shared/overviewUiLabels';
import type { TimetableOverviewIconUrls, TimetableRailGroup } from '../../types/timetableOverview';
import { trainTypeIconUrl } from '../../utils/overviewGrid';
import MrtOverviewRailGroupGrid from './MrtOverviewRailGroupGrid.vue';

defineProps<{
  group: TimetableRailGroup;
  iconUrls: TimetableOverviewIconUrls;
  labels: OverviewUiLabels;
}>();
</script>

<template>
  <MrtOverviewRailGroupGrid :group="group" :icon-urls="iconUrls" :labels="labels">
    <template #time-cell="{ row, track, columnIndex }">
      {{ row.cells[columnIndex].text }}
    </template>
    <template #transfer-cell="{ row, track, columnIndex }">
      <div
        v-for="(v, vi) in row.cells[columnIndex].vehicles"
        :key="vi"
        class="mrt-ov-vehicle"
      >
        <img
          v-if="trainTypeIconUrl(iconUrls, v.iconKey)"
          class="mrt-ov-icon"
          :src="trainTypeIconUrl(iconUrls, v.iconKey)"
          :alt="v.typeName"
          width="32"
          height="32"
        />
        <span class="mrt-ov-vehicle-type">{{ v.typeName }}</span>
        <span class="mrt-ov-vehicle-num">{{ v.serviceNumber }}</span>
        <span v-if="v.detail" class="mrt-ov-vehicle-detail">{{ v.detail }}</span>
      </div>
    </template>
  </MrtOverviewRailGroupGrid>
</template>
