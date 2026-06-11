<script setup lang="ts">
import type { OverviewUiLabels } from '../../shared/overviewUiLabels';
import type { TimetableOverviewIconUrls, TimetableRailGroup } from '../../types/timetableOverview';
import { trainTypeIconUrl } from '../../utils/overviewGrid';
import { overviewColumnIsCancelled } from '../../shared/overviewCancelled';
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
    </template>
    <template #transfer-cell="{ row, columnIndex }">
      <template v-if="row.kind === 'trainChangeType'">
        <template v-for="(v, vi) in row.cells[columnIndex].vehicles" :key="vi">
          <img
            v-if="trainTypeIconUrl(iconUrls, v.iconKey)"
            class="mrt-ov-icon mrt-ov-icon--head"
            :src="trainTypeIconUrl(iconUrls, v.iconKey)"
            :alt="v.typeName"
            width="28"
            height="28"
          />
          <span class="mrt-ov-col-head__type-name">{{ v.typeName }}</span>
        </template>
      </template>
      <template v-else-if="row.kind === 'trainChangeNumber'">
        <span
          v-for="(v, vi) in row.cells[columnIndex].vehicles"
          :key="vi"
          class="mrt-ov-transfer-num"
        >
          {{ v.serviceNumber }}
        </span>
      </template>
      <template v-else>
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
    </template>
  </MrtOverviewRailGroupGrid>
</template>
