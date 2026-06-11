<script setup lang="ts">
import type { TimetableOverviewIconUrls, TimetableOverviewRow } from '../../types/timetableOverview';
import { trainTypeIconUrl } from '../../utils/overviewGrid';

type TransferRow = Extract<
  TimetableOverviewRow,
  { kind: 'trainChangeType' | 'trainChangeNumber' | 'busConnection' }
>;

const props = defineProps<{
  row: TransferRow;
  columnIndex: number;
  iconUrls: TimetableOverviewIconUrls;
}>();

function vehicleIconUrl(iconKey: string): string {
  return trainTypeIconUrl(props.iconUrls, iconKey);
}
</script>

<template>
  <template v-if="row.kind === 'trainChangeType'">
    <template v-for="(v, vi) in row.cells[columnIndex].vehicles" :key="vi">
      <img
        v-if="vehicleIconUrl(v.iconKey)"
        class="mrt-ov-icon mrt-ov-icon--head"
        :src="vehicleIconUrl(v.iconKey)"
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
        v-if="vehicleIconUrl(v.iconKey)"
        class="mrt-ov-icon"
        :src="vehicleIconUrl(v.iconKey)"
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
