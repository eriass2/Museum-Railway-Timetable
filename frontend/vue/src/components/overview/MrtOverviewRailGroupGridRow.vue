<script setup lang="ts">
import { computed } from 'vue';
import type {
  TimetableOverviewIconUrls,
  TimetableOverviewRow,
  TimetableRailGroup,
} from '../../types/timetableOverview';
import type { OverviewGridTrack, HighlightStripeSpan } from '../../utils/overviewGrid';
import {
  highlightStripeSpanStyle,
  isBusRow,
  isTimeRow,
  isTransferRow,
  overviewGridCellStyle,
  overviewRowClass,
  overviewStationColumnStyle,
} from '../../utils/overviewGrid';

const props = defineProps<{
  row: TimetableOverviewRow;
  rowIndex: number;
  gridTracks: OverviewGridTrack[];
  busIconUrl: string | undefined;
  showDeviationMeta: boolean;
  editableCells: boolean;
  columnCancelled: (index: number) => boolean;
  stripeSpan: (rowIndex: number, trackIndex: number) => HighlightStripeSpan | null;
}>();

const rowClass = computed(() => overviewRowClass(props.row, props.rowIndex));
</script>

<template>
  <div class="mrt-ov-grid-row" :class="rowClass">
    <div
      class="mrt-ov-station-col"
      :class="{ 'mrt-ov-station-col--bus': isBusRow(row) }"
      :style="overviewStationColumnStyle()"
    >
      <img
        v-if="isBusRow(row) && busIconUrl"
        class="mrt-ov-bus-station-icon"
        :src="busIconUrl"
        alt=""
        width="20"
        height="20"
      />
      <span>{{ row.label }}</span>
    </div>
    <template v-if="isTimeRow(row)">
      <template v-for="(track, ti) in gridTracks" :key="`time-${rowIndex}-${ti}`">
        <div
          v-if="track.kind === 'highlight' && stripeSpan(rowIndex, ti)"
          class="mrt-ov-highlight-stripe mrt-ov-highlight-stripe--span"
          :style="highlightStripeSpanStyle(stripeSpan(rowIndex, ti)!)"
          :aria-label="showDeviationMeta ? stripeSpan(rowIndex, ti)!.label : undefined"
        >
          <span v-if="showDeviationMeta" class="mrt-ov-highlight-stripe__label" aria-hidden="true">
            {{ stripeSpan(rowIndex, ti)!.label }}
          </span>
        </div>
        <div
          v-else-if="track.kind === 'train'"
          class="mrt-ov-time-cell"
          :class="{
            'mrt-ov-time-cell--edit': editableCells && row.stationId,
            'mrt-ov-time-cell--cancelled': columnCancelled(track.columnIndex),
          }"
          :style="overviewGridCellStyle(ti)"
        >
          <slot name="time-cell" :row="row" :track="track" :column-index="track.columnIndex" />
        </div>
      </template>
    </template>
    <template v-else-if="isTransferRow(row)">
      <template v-for="(track, ti) in gridTracks" :key="`xfer-${rowIndex}-${ti}`">
        <div
          v-if="track.kind === 'train'"
          class="mrt-ov-transfer-cell"
          :class="{
            'mrt-ov-transfer-cell--empty': !row.cells[track.columnIndex].vehicles.length,
            'mrt-ov-transfer-cell--change-type': row.kind === 'trainChangeType',
            'mrt-ov-transfer-cell--change-number': row.kind === 'trainChangeNumber',
          }"
          :style="overviewGridCellStyle(ti)"
        >
          <slot name="transfer-cell" :row="row" :track="track" :column-index="track.columnIndex" />
        </div>
      </template>
    </template>
  </div>
</template>

<style scoped>
.mrt-ov-time-cell--edit {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 2rem;
}
</style>
