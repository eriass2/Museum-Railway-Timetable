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

<style scoped>
@import './overviewStatus.css';

.mrt-ov-icon {
  width: 2.1rem;
  height: auto;
  object-fit: contain;
}

.mrt-ov-icon--head {
  width: 1.45rem;
  max-height: 1.45rem;
}

.mrt-ov-col-head__type-name {
  max-width: 100%;
  overflow-wrap: anywhere;
}

.mrt-ov-transfer-num {
  font-variant-numeric: tabular-nums;
}

.mrt-ov-vehicle {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.1rem;
}

.mrt-ov-vehicle + .mrt-ov-vehicle {
  margin-top: 0.25rem;
  padding-top: 0.25rem;
  border-top: 1px dashed #bbb;
}

.mrt-ov-vehicle-type {
  font-size: var(--mrt-ov-text-size, 0.5rem);
  font-weight: 700;
}

.mrt-ov-vehicle-num {
  font-size: var(--mrt-ov-num-size, 1rem);
  font-weight: 700;
}

.mrt-ov-vehicle-detail {
  font-size: var(--mrt-ov-text-size, 0.5rem);
  line-height: 1.25;
}
</style>
