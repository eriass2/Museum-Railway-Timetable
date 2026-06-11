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
  overviewGridStyle,
  trainTypeIconUrl,
} from '../../utils/overviewGrid';
import MrtOverviewRailGroupGridHead from './MrtOverviewRailGroupGridHead.vue';
import MrtOverviewRailGroupGridRow from './MrtOverviewRailGroupGridRow.vue';

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

        <MrtOverviewRailGroupGridRow
          v-for="(row, ri) in group.rows"
          :key="ri"
          :row="row"
          :row-index="ri"
          :grid-tracks="gridTracks"
          :bus-icon-url="busIconUrl"
          :show-deviation-meta="showDeviationMeta"
          :editable-cells="editableCells"
          :column-cancelled="columnCancelled"
          :stripe-span="stripeSpan"
        >
          <template #time-cell="slotProps">
            <slot name="time-cell" v-bind="slotProps" />
          </template>
          <template #transfer-cell="slotProps">
            <slot name="transfer-cell" v-bind="slotProps" />
          </template>
        </MrtOverviewRailGroupGridRow>
      </div>
    </div>
  </section>
</template>
