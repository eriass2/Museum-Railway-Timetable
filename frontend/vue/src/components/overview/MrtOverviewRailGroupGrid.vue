<script setup lang="ts">
import { computed } from 'vue';
import type { TimetableOverviewIconUrls, TimetableRailGroup } from '../../types/timetableOverview';
import type { OverviewUiLabels } from '../../shared/overviewUiLabels';
import { formatDeviationPlanned } from '../../shared/overviewUiLabels';
import { overviewColumnIsCancelled } from '../../shared/overviewCancelled';
import { ROAD_BUS_TRAIN_TYPE_SLUG } from '../../shared/trainTypeIcons';
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
  overviewHeadRowStyle,
  overviewHighlightStripeStyle,
  overviewRowClass,
  overviewStationColumnStyle,
  trainTypeIconUrl,
} from '../../utils/overviewGrid';

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

function deviationTitle(plannedName: string | undefined): string {
  if (plannedName) {
    return formatDeviationPlanned(props.labels.deviationPlanned, plannedName);
  }
  return props.labels.deviationFromPlan;
}

const gridTracks = computed(() => buildOverviewGridTracks(props.group.columns));
const gridStyle = computed(() => overviewGridStyle(props.group.columns));
const highlightSpans = computed(() => buildHighlightStripeSpans(props.group.rows, gridTracks.value));
const busIconUrl = computed(() => trainTypeIconUrl(props.iconUrls, ROAD_BUS_TRAIN_TYPE_SLUG));

function stripeSpan(rowIndex: number, trackIndex: number) {
  return highlightStripeSpanAt(highlightSpans.value, rowIndex, trackIndex);
}

function columnAt(index: number) {
  return props.group.columns[index];
}

function columnCancelled(index: number): boolean {
  const column = columnAt(index);
  return column ? overviewColumnIsCancelled(column) : false;
}

function cancelledNoticeDetail(index: number): boolean {
  const column = columnAt(index);
  if (!column || !overviewColumnIsCancelled(column)) {
    return false;
  }
  const notice = column.deviationNotice?.trim() || '';
  if (!notice) {
    return false;
  }
  return notice.toLowerCase() !== props.labels.cancelledLabel.toLowerCase();
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
        <div class="mrt-ov-grid-row mrt-ov-grid-row--head">
          <div class="mrt-ov-station-col" :style="{ ...overviewStationColumnStyle(), ...overviewHeadRowStyle(1) }">
            Station
          </div>
          <template v-for="(track, ti) in gridTracks" :key="`type-${ti}`">
            <div
              v-if="track.kind === 'highlight'"
              class="mrt-ov-highlight-stripe mrt-ov-highlight-stripe--head"
              :style="{ ...overviewGridCellStyle(ti), ...overviewHeadRowStyle(1), ...overviewHighlightStripeStyle(track.color) }"
              aria-hidden="true"
            />
            <div
              v-else
              class="mrt-ov-col-head mrt-ov-col-head--type"
              :class="{ 'mrt-ov-col-head--cancelled': columnCancelled(track.columnIndex) }"
              :style="{ ...overviewGridCellStyle(ti), ...overviewHeadRowStyle(1) }"
            >
              <img
                v-if="trainTypeIconUrl(iconUrls, group.columns[track.columnIndex].iconKey)"
                class="mrt-ov-icon mrt-ov-icon--head"
                :class="{ 'mrt-ov-icon--cancelled': columnCancelled(track.columnIndex) }"
                :src="trainTypeIconUrl(iconUrls, group.columns[track.columnIndex].iconKey)"
                :alt="group.columns[track.columnIndex].trainTypeName"
                width="28"
                height="28"
              />
              <span class="mrt-ov-col-head__type-name">
                {{ group.columns[track.columnIndex].trainTypeName }}
                <abbr
                  v-if="showDeviationMeta && group.columns[track.columnIndex].isDeviation"
                  class="mrt-ov-deviation-mark"
                  :title="deviationTitle(group.columns[track.columnIndex].plannedTrainTypeName)"
                >†</abbr>
              </span>
            </div>
          </template>
        </div>

        <div class="mrt-ov-grid-row mrt-ov-grid-row--head">
          <div
            class="mrt-ov-station-col"
            :style="{ ...overviewStationColumnStyle(), ...overviewHeadRowStyle(2) }"
          >
            {{ labels.colTrip }}
          </div>
          <template v-for="(track, ti) in gridTracks" :key="`num-${ti}`">
            <div
              v-if="track.kind === 'highlight'"
              class="mrt-ov-highlight-stripe mrt-ov-highlight-stripe--head"
              :style="{ ...overviewGridCellStyle(ti), ...overviewHeadRowStyle(2), ...overviewHighlightStripeStyle(track.color) }"
              aria-hidden="true"
            />
            <div
              v-else
              class="mrt-ov-col-head mrt-ov-col-head--number"
              :class="{ 'mrt-ov-col-head--cancelled': columnCancelled(track.columnIndex) }"
              :style="{ ...overviewGridCellStyle(ti), ...overviewHeadRowStyle(2) }"
            >
              {{ group.columns[track.columnIndex].serviceNumber }}
              <span
                v-if="showDeviationMeta && columnCancelled(track.columnIndex)"
                class="mrt-ov-cancelled-badge"
              >
                {{ labels.cancelledLabel }}
              </span>
              <span
                v-if="showDeviationMeta && cancelledNoticeDetail(track.columnIndex)"
                class="mrt-ov-deviation-note mrt-ov-deviation-note--cancelled-detail"
              >
                {{ group.columns[track.columnIndex].deviationNotice }}
              </span>
              <span
                v-else-if="
                  showDeviationMeta &&
                  group.columns[track.columnIndex].deviationNotice &&
                  !columnCancelled(track.columnIndex)
                "
                class="mrt-ov-deviation-note"
              >
                {{ group.columns[track.columnIndex].deviationNotice }}
              </span>
            </div>
          </template>
        </div>

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
