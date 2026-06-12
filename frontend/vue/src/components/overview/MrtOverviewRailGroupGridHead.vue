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

<style scoped>
@import './overviewStatus.css';

.mrt-ov-grid-row--head .mrt-ov-station-col,
.mrt-ov-grid-row--head .mrt-ov-col-head {
  background: var(--mrt-ov-highlight-strong);
  font-weight: 700;
}

.mrt-ov-grid-row--head + .mrt-ov-grid-row--head .mrt-ov-col-head {
  background: var(--mrt-ov-highlight);
}

.mrt-ov-grid-row--head + .mrt-ov-grid-row--head .mrt-ov-station-col {
  background: var(--mrt-ov-highlight);
}

.mrt-ov-col-head--number {
  font-size: var(--mrt-ov-num-size);
  font-weight: 700;
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

.mrt-ov-grid-row--head .mrt-ov-station-col {
  z-index: 3;
  font-weight: 600;
}

.mrt-ov-col-head {
  padding: 0.4rem 0.25rem;
  border: 1px solid var(--mrt-border-default, #ccc);
  text-align: center;
  font-size: var(--mrt-ov-num-size);
  font-weight: 600;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.2rem;
  min-width: 0;
}

.mrt-ov-col-head--type {
  padding: 0.28rem 0.12rem;
  gap: 0.12rem;
  font-size: calc(var(--mrt-ov-num-size) * 0.72);
  line-height: 1.15;
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

.mrt-ov-highlight-stripe--head {
  min-height: 0;
  padding-block: 0.25rem;
  padding-inline: 1px;
}

.mrt-ov-col-head__type-name {
  max-width: 100%;
  overflow-wrap: anywhere;
}

.mrt-ov-icon {
  width: 2.1rem;
  height: auto;
  object-fit: contain;
}

.mrt-ov-icon--head {
  width: 1.45rem;
  max-height: 1.45rem;
}

@media (max-width: 40rem) {
  .mrt-ov-icon {
    width: 1.75rem;
  }

  .mrt-ov-icon--head {
    width: 1.3rem;
    max-height: 1.3rem;
  }
}
</style>
