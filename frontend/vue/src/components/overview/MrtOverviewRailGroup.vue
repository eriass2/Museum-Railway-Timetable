<script setup lang="ts">
import type { OverviewUiLabels } from '../../shared/overviewUiLabels';
import type { TimetableOverviewIconUrls, TimetableRailGroup } from '../../types/timetableOverview';
import { trainTypeIconUrl } from '../../utils/overviewGrid';
import { overviewColumnIsCancelled } from '../../shared/overviewCancelled';
import { ROAD_BUS_TRAIN_TYPE_SLUG } from '../../shared/trainTypeIcons';
import MrtOverviewRailGroupGrid from './MrtOverviewRailGroupGrid.vue';
import MrtOverviewTimeDisplay from './MrtOverviewTimeDisplay.vue';

defineProps<{
  group: TimetableRailGroup;
  iconUrls: TimetableOverviewIconUrls;
  labels: OverviewUiLabels;
}>();
</script>

<template>
  <MrtOverviewRailGroupGrid :group="group" :icon-urls="iconUrls" :labels="labels">
    <template #time-cell="{ row, track, columnIndex }">
      <MrtOverviewTimeDisplay
        :text="row.cells[columnIndex].text"
        :approximate-time="row.cells[columnIndex].approximateTime"
        :cancelled="overviewColumnIsCancelled(group.columns[columnIndex])"
      />
      <span v-if="row.cells[columnIndex].busServiceNumber" class="mrt-ov-bus-ref">
        <img
          v-if="trainTypeIconUrl(iconUrls, ROAD_BUS_TRAIN_TYPE_SLUG)"
          class="mrt-ov-bus-ref__icon"
          :src="trainTypeIconUrl(iconUrls, ROAD_BUS_TRAIN_TYPE_SLUG)"
          alt=""
          width="20"
          height="20"
        />
        <span class="mrt-ov-bus-ref__num">{{ row.cells[columnIndex].busServiceNumber }}</span>
      </span>
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
