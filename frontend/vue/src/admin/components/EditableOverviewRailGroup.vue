<script setup lang="ts">
import { computed } from 'vue';
import type { TimetableOverviewIconUrls, TimetableRailGroup, TimetableTimeCellEdit } from '../../types/timetableOverview';
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
import type { OverviewGridEdit } from '../composables/useOverviewGridEdit';

const props = defineProps<{
  group: TimetableRailGroup;
  iconUrls: TimetableOverviewIconUrls;
  editor: OverviewGridEdit;
  readonly?: boolean;
}>();

const gridTracks = computed(() => buildOverviewGridTracks(props.group.columns));
const gridStyle = computed(() => overviewGridStyle(props.group.columns));
const highlightSpans = computed(() => buildHighlightStripeSpans(props.group.rows, gridTracks.value));

function stripeSpan(rowIndex: number, trackIndex: number) {
  return highlightStripeSpanAt(highlightSpans.value, rowIndex, trackIndex);
}

function showDeparture(kind: string): boolean {
  return kind === 'from' || kind === 'departure' || kind === 'station';
}

function showArrival(kind: string): boolean {
  return kind === 'to' || kind === 'arrival' || kind === 'station';
}

async function patchCell(
  serviceId: number,
  stationId: number,
  cell: TimetableTimeCellEdit | undefined,
  patch: Partial<TimetableTimeCellEdit>,
) {
  const merged = props.editor.mergeEdit(serviceId, stationId, cell, patch);
  await props.editor.applyCellEdit(serviceId, stationId, merged);
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
              <span>{{ group.columns[track.columnIndex].trainTypeName }}</span>
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
              />
              <div
                v-else-if="track.kind === 'train'"
                class="mrt-ov-time-cell"
                :class="{ 'mrt-ov-time-cell--edit': !readonly && row.stationId }"
                :style="overviewGridCellStyle(ti)"
              >
                <template v-if="readonly || !row.stationId || !group.columns[track.columnIndex].serviceId">
                  {{ row.cells[track.columnIndex].text }}
                </template>
                <template v-else>
                  <label class="mrt-ov-edit-stop">
                    <input
                      type="checkbox"
                      :checked="row.cells[track.columnIndex].edit?.stopsHere ?? false"
                      @change="patchCell(
                        group.columns[track.columnIndex].serviceId!,
                        row.stationId!,
                        row.cells[track.columnIndex].edit,
                        { stopsHere: ($event.target as HTMLInputElement).checked },
                      )"
                    />
                    Stannar
                  </label>
                  <input
                    v-if="showDeparture(row.kind)"
                    type="time"
                    class="mrt-ov-edit-time"
                    :value="editor.hhmmToInput(row.cells[track.columnIndex].edit?.departure ?? '')"
                    @change="patchCell(
                      group.columns[track.columnIndex].serviceId!,
                      row.stationId!,
                      row.cells[track.columnIndex].edit,
                      { departure: editor.inputToHhmm(($event.target as HTMLInputElement).value), stopsHere: true },
                    )"
                  />
                  <input
                    v-if="showArrival(row.kind)"
                    type="time"
                    class="mrt-ov-edit-time"
                    :value="editor.hhmmToInput(row.cells[track.columnIndex].edit?.arrival ?? '')"
                    @change="patchCell(
                      group.columns[track.columnIndex].serviceId!,
                      row.stationId!,
                      row.cells[track.columnIndex].edit,
                      { arrival: editor.inputToHhmm(($event.target as HTMLInputElement).value), stopsHere: true },
                    )"
                  />
                  <span class="mrt-ov-edit-pa">
                    <label>
                      <input
                        type="checkbox"
                        :checked="row.cells[track.columnIndex].edit?.pickupAllowed ?? true"
                        @change="patchCell(
                          group.columns[track.columnIndex].serviceId!,
                          row.stationId!,
                          row.cells[track.columnIndex].edit,
                          { pickupAllowed: ($event.target as HTMLInputElement).checked },
                        )"
                      />
                      P
                    </label>
                    <label>
                      <input
                        type="checkbox"
                        :checked="row.cells[track.columnIndex].edit?.dropoffAllowed ?? true"
                        @change="patchCell(
                          group.columns[track.columnIndex].serviceId!,
                          row.stationId!,
                          row.cells[track.columnIndex].edit,
                          { dropoffAllowed: ($event.target as HTMLInputElement).checked },
                        )"
                      />
                      A
                    </label>
                  </span>
                </template>
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
                <span v-if="!row.cells[track.columnIndex].vehicles.length">—</span>
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
.mrt-ov-edit-time {
  width: 100%;
  max-width: 6rem;
  font-size: 0.75rem;
}
.mrt-ov-edit-pa {
  display: flex;
  gap: 0.35rem;
  justify-content: center;
}
.mrt-ov-edit-stop {
  display: flex;
  align-items: center;
  gap: 0.2rem;
  justify-content: center;
}
</style>
