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

<style scoped>
.mrt-ov-group {
  margin-bottom: var(--mrt-spacing-xl, 2rem);
  border: 1px solid var(--mrt-border-default, #d8d8d8);
  box-shadow: var(--mrt-shadow-md, 0 2px 10px rgba(0, 0, 0, 0.06));
  min-width: 0;
  max-width: 100%;
  overflow: hidden;
}

.mrt-ov-route-header {
  padding: 0.85rem var(--mrt-spacing-md, 1rem);
  background: var(--mrt-ov-header-bg);
  color: var(--mrt-ov-header-fg);
}

.mrt-ov-route-title {
  margin: 0 0 0.25rem;
  font-size: 1.15rem;
  font-weight: 700;
  line-height: 1.2;
}

.mrt-ov-route-ends {
  margin: 0;
  font-size: 0.9rem;
  opacity: 0.95;
}

.mrt-ov-route-arrow {
  margin: 0 0.35rem;
}

.mrt-ov-grid-scroll {
  width: 100%;
  max-width: 100%;
  min-width: 0;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.mrt-ov-grid {
  --mrt-ov-station-w: 10.5rem;
  --mrt-ov-col-min: 3.35rem;
  --mrt-ov-col-max: 4.1rem;
  --mrt-ov-highlight-w: 1.15rem;
  --mrt-ov-num-size: 1rem;
  --mrt-ov-footnote-size: calc(var(--mrt-ov-num-size) * 0.5);
  --mrt-ov-text-size: var(--mrt-ov-footnote-size);
  display: grid;
  width: max(100%, var(--mrt-ov-grid-min, 30rem));
  background: #fff;
}

@media (max-width: 40rem) {
  .mrt-ov-route-header {
    padding-inline: 0.75rem;
  }

  .mrt-ov-route-title {
    font-size: 1.05rem;
  }

  .mrt-ov-route-ends {
    font-size: 0.85rem;
  }

  .mrt-ov-grid {
    --mrt-ov-station-w: 8.5rem;
    --mrt-ov-col-min: 3.25rem;
    --mrt-ov-num-size: 0.9rem;
  }
}
</style>
