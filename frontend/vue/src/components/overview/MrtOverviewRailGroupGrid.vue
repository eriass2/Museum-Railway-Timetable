<script setup lang="ts">
import { computed } from 'vue';
import type { TimetableOverviewIconUrls, TimetableRailGroup } from '../../types/timetableOverview';
import type { OverviewUiLabels } from '../../shared/overviewUiLabels';
import { ROAD_BUS_TRAIN_TYPE_SLUG } from '../../shared/trainTypeIcons';
import { overviewColumnCancelled } from '../../utils/overviewColumnDisplay';
import {
  buildHighlightStripeSpans,
  buildOverviewGridTracks,
  highlightStripeSpanAt,
  highlightStripeSpanStyle,
  isBusRow,
  isTimeRow,
  isTransferRow,
  overviewGridCellStyle,
  overviewGridStyle,
  overviewRowClass,
  overviewStationColumnStyle,
  trainTypeIconUrl,
} from '../../utils/overviewGrid';
import MrtOverviewRailGroupGridHead from './MrtOverviewRailGroupGridHead.vue';

const props = withDefaults(
  defineProps<{
    group: TimetableRailGroup;
    iconUrls: TimetableOverviewIconUrls;
    labels: OverviewUiLabels;
    showDeviationMeta?: boolean;
    editableCells?: boolean;
  }>(),
  { showDeviationMeta: true, editableCells: false },
);

const gridTracks = computed(() => buildOverviewGridTracks(props.group.columns));
const gridStyle = computed(() => overviewGridStyle(props.group.columns));
const highlightSpans = computed(() => buildHighlightStripeSpans(props.group.rows, gridTracks.value));
const busIconUrl = computed(() => trainTypeIconUrl(props.iconUrls, ROAD_BUS_TRAIN_TYPE_SLUG));

function stripeSpan(rowIndex: number, trackIndex: number) {
  return highlightStripeSpanAt(highlightSpans.value, rowIndex, trackIndex);
}

function columnCancelled(index: number): boolean {
  return overviewColumnCancelled(props.group, index);
}
</script>

<template>
  <section class="mrt-ov-group">
    <header class="mrt-ov-route-header">
      <h3 class="mrt-ov-route-title">{{ group.routeLabel }}</h3>
      <p class="mrt-ov-route-ends">
        <span>{{ group.fromLabel }}</span>
        <span class="mrt-ov-route-arrow" aria-hidden="true">→</span>
        <span>{{ group.toLabel }}</span>
      </p>
    </header>

    <div class="mrt-ov-grid-scroll">
      <div class="mrt-ov-grid" :style="gridStyle">
        <MrtOverviewRailGroupGridHead
          :group="group"
          :grid-tracks="gridTracks"
          :icon-urls="iconUrls"
          :labels="labels"
          :show-deviation-meta="showDeviationMeta"
        />

        <div
          v-for="(row, ri) in group.rows"
          :key="ri"
          class="mrt-ov-grid-row"
          :class="overviewRowClass(row, ri)"
        >
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
            <template v-for="(track, ti) in gridTracks" :key="`time-${ri}-${ti}`">
              <div
                v-if="track.kind === 'highlight' && stripeSpan(ri, ti)"
                class="mrt-ov-highlight-stripe mrt-ov-highlight-stripe--span"
                :style="highlightStripeSpanStyle(stripeSpan(ri, ti)!)"
                :aria-label="showDeviationMeta ? stripeSpan(ri, ti)!.label : undefined"
              >
                <span v-if="showDeviationMeta" class="mrt-ov-highlight-stripe__label" aria-hidden="true">
                  {{ stripeSpan(ri, ti)!.label }}
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
                <slot
                  name="time-cell"
                  :row="row"
                  :track="track"
                  :column-index="track.columnIndex"
                />
              </div>
            </template>
          </template>
          <template v-else-if="isTransferRow(row)">
            <template v-for="(track, ti) in gridTracks" :key="`xfer-${ri}-${ti}`">
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
                <slot
                  name="transfer-cell"
                  :row="row"
                  :track="track"
                  :column-index="track.columnIndex"
                />
              </div>
            </template>
          </template>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.mrt-ov-time-cell--edit {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 2rem;
}
</style>
