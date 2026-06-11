<script setup lang="ts">
import type { TimetableOverviewIconUrls, TimetableRailGroup } from '../../types/timetableOverview';
import type { OverviewUiLabels } from '../../shared/overviewUiLabels';
import {
  overviewCancelledNoticeDetail,
  overviewColumnCancelled,
  overviewDeviationTitle,
} from '../../utils/overviewColumnDisplay';
import type { OverviewGridTrack } from '../../utils/overviewGrid';
import {
  overviewGridCellStyle,
  overviewHeadRowStyle,
  overviewHighlightStripeStyle,
  overviewStationColumnStyle,
  trainTypeIconUrl,
} from '../../utils/overviewGrid';

const props = defineProps<{
  group: TimetableRailGroup;
  gridTracks: OverviewGridTrack[];
  iconUrls: TimetableOverviewIconUrls;
  labels: OverviewUiLabels;
  showDeviationMeta: boolean;
}>();

function columnCancelled(index: number): boolean {
  return overviewColumnCancelled(props.group, index);
}

function cancelledNoticeDetail(index: number): boolean {
  return overviewCancelledNoticeDetail(props.group, index, props.labels.cancelledLabel);
}

function columnIconKey(columnIndex: number): string {
  return props.group.columns[columnIndex].iconKey;
}
</script>

<template>
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
          v-if="trainTypeIconUrl(iconUrls, columnIconKey(track.columnIndex))"
          class="mrt-ov-icon mrt-ov-icon--head"
          :class="{ 'mrt-ov-icon--cancelled': columnCancelled(track.columnIndex) }"
          :src="trainTypeIconUrl(iconUrls, columnIconKey(track.columnIndex))"
          :alt="group.columns[track.columnIndex].trainTypeName"
          width="28"
          height="28"
        />
        <span class="mrt-ov-col-head__type-name">
          {{ group.columns[track.columnIndex].trainTypeName }}
          <abbr
            v-if="showDeviationMeta && group.columns[track.columnIndex].isDeviation"
            class="mrt-ov-deviation-mark"
            :title="overviewDeviationTitle(labels, group.columns[track.columnIndex].plannedTrainTypeName)"
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
        <template v-if="group.columns[track.columnIndex].isStandaloneBus">&nbsp;</template>
        <template v-else>{{ group.columns[track.columnIndex].serviceNumber }}</template>
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
</template>
