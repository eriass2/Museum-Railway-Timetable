<script setup lang="ts">
import { computed } from 'vue';
import type { TimetableOverviewIconUrls, TimetableRailGroup } from '../../types/timetableOverview';
import {
  buildHighlightStripeSpans,
  buildOverviewGridTracks,
  highlightStripeSpanAt,
  highlightStripeSpanStyle,
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
    showDeviationMeta?: boolean;
    editableCells?: boolean;
  }>(),
  { showDeviationMeta: true, editableCells: false },
);

const gridTracks = computed(() => buildOverviewGridTracks(props.group.columns));
const gridStyle = computed(() => overviewGridStyle(props.group.columns));
const highlightSpans = computed(() => buildHighlightStripeSpans(props.group.rows, gridTracks.value));

function stripeSpan(rowIndex: number, trackIndex: number) {
  return highlightStripeSpanAt(highlightSpans.value, rowIndex, trackIndex);
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
              class="mrt-ov-col-head"
              :style="{ ...overviewGridCellStyle(ti), ...overviewHeadRowStyle(1) }"
            >
              <img
                v-if="trainTypeIconUrl(iconUrls, group.columns[track.columnIndex].iconKey)"
                class="mrt-ov-icon"
                :src="trainTypeIconUrl(iconUrls, group.columns[track.columnIndex].iconKey)"
                :alt="group.columns[track.columnIndex].trainTypeName"
                width="36"
                height="36"
              />
              <span>
                {{ group.columns[track.columnIndex].trainTypeName }}
                <abbr
                  v-if="showDeviationMeta && group.columns[track.columnIndex].isDeviation"
                  class="mrt-ov-deviation-mark"
                  :title="group.columns[track.columnIndex].plannedTrainTypeName ? `Planerat: ${group.columns[track.columnIndex].plannedTrainTypeName}` : 'Avvikelse från planerad tågtyp'"
                >†</abbr>
              </span>
            </div>
          </template>
        </div>

        <div class="mrt-ov-grid-row mrt-ov-grid-row--head">
          <div
            class="mrt-ov-station-col mrt-ov-station-col--empty"
            :style="{ ...overviewStationColumnStyle(), ...overviewHeadRowStyle(2) }"
            aria-hidden="true"
          />
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
              :style="{ ...overviewGridCellStyle(ti), ...overviewHeadRowStyle(2) }"
            >
              {{ group.columns[track.columnIndex].serviceNumber }}
              <span
                v-if="showDeviationMeta && group.columns[track.columnIndex].deviationNotice"
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
          <div class="mrt-ov-station-col" :style="overviewStationColumnStyle()">{{ row.label }}</div>
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
                :class="{ 'mrt-ov-time-cell--edit': editableCells && row.stationId }"
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
                :class="{ 'mrt-ov-transfer-cell--empty': !row.cells[track.columnIndex].vehicles.length }"
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
  flex-direction: column;
  gap: 0.2rem;
  font-size: 0.75rem;
}
</style>
