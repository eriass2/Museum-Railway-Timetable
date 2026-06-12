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
@import './overviewStatus.css';

.mrt-ov-grid-row {
  display: contents;
}

.mrt-ov-station-col {
  position: sticky;
  left: 0;
  z-index: 2;
  padding: var(--mrt-cell-padding-md, 0.4rem 0.55rem);
  border: 1px solid var(--mrt-border-default, #ccc);
  font-size: var(--mrt-ov-num-size);
  font-weight: 400;
  background: #fff;
  border-right-width: 2px;
}

.mrt-ov-grid-row--from .mrt-ov-station-col,
.mrt-ov-grid-row--from .mrt-ov-time-cell,
.mrt-ov-grid-row--to .mrt-ov-station-col,
.mrt-ov-grid-row--to .mrt-ov-time-cell {
  background: var(--mrt-ov-highlight);
}

.mrt-ov-grid-row--alt .mrt-ov-station-col,
.mrt-ov-grid-row--alt .mrt-ov-time-cell {
  background: var(--mrt-ov-stripe);
}

.mrt-ov-highlight-stripe {
  display: flex;
  align-items: center;
  justify-content: center;
  padding-block: 0.35rem;
  padding-inline: 1px;
  border: 1px solid var(--mrt-border-default, #ccc);
  background: var(--mrt-ov-cell-highlight, var(--mrt-special-bg, #fff9c4));
  min-height: 2.5rem;
}

.mrt-ov-highlight-stripe--span {
  align-self: stretch;
  min-height: 0;
  padding-block: 0.5rem;
  padding-inline: 1px;
}

.mrt-ov-highlight-stripe__label {
  display: block;
  font-size: var(--mrt-ov-footnote-size);
  font-weight: 700;
  line-height: 1.1;
  text-align: center;
  writing-mode: vertical-rl;
  text-orientation: mixed;
  transform: rotate(180deg);
}

.mrt-ov-highlight-stripe--span .mrt-ov-highlight-stripe__label {
  line-height: 1.15;
  letter-spacing: 0.01em;
}

.mrt-ov-grid-row--transfer .mrt-ov-station-col,
.mrt-ov-grid-row--transfer .mrt-ov-transfer-cell:not(.mrt-ov-transfer-cell--empty) {
  background: var(--mrt-ov-transfer);
}

.mrt-ov-grid-row--transfer-type .mrt-ov-station-col {
  background: var(--mrt-ov-transfer-label);
  font-weight: 600;
}

.mrt-ov-grid-row--transfer-number .mrt-ov-station-col {
  background: var(--mrt-ov-transfer);
  min-height: 0;
}

.mrt-ov-grid-row--bus .mrt-ov-station-col,
.mrt-ov-grid-row--bus .mrt-ov-time-cell {
  background: var(--mrt-ov-bus-bg);
}

.mrt-ov-grid-row--bus .mrt-ov-station-col {
  background: var(--mrt-ov-bus-label);
  font-weight: 400;
}

.mrt-ov-station-col--bus {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.mrt-ov-bus-station-icon {
  width: 1.2rem;
  height: auto;
  flex-shrink: 0;
  object-fit: contain;
}

.mrt-ov-grid-row--transfer .mrt-ov-transfer-cell--empty {
  background: #fff;
  min-height: 0;
  padding-block: 0.25rem;
}

.mrt-ov-time-cell,
.mrt-ov-transfer-cell {
  padding: var(--mrt-cell-padding-md, 0.4rem 0.3rem);
  border: 1px solid var(--mrt-border-default, #ccc);
  text-align: center;
  font-size: var(--mrt-ov-num-size);
  font-variant-numeric: tabular-nums;
  line-height: 1;
  white-space: nowrap;
  min-width: 0;
}

.mrt-ov-transfer-cell {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  min-height: 3.5rem;
}

.mrt-ov-transfer-cell--change-type {
  min-height: 0;
  padding: 0.28rem 0.12rem;
  gap: 0.12rem;
  font-size: calc(var(--mrt-ov-num-size) * 0.72);
  line-height: 1.15;
}

.mrt-ov-transfer-cell--change-number {
  min-height: 0;
  padding: 0.25rem 0.12rem;
  font-size: var(--mrt-ov-num-size);
  font-weight: 700;
}

.mrt-ov-time-cell--edit {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 2rem;
}
</style>
