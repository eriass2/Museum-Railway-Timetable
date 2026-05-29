<script setup lang="ts">
import type { TimetableOverviewIconUrls, TimetableRailGroup } from '../../types/timetableOverview';
import { isTimeRow, isTransferRow, overviewHighlightStyle, overviewRowClass, trainTypeIconUrl } from '../../utils/overviewGrid';

defineProps<{
  group: TimetableRailGroup;
  iconUrls: TimetableOverviewIconUrls;
}>();
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
      <div class="mrt-ov-grid" :style="{ '--mrt-ov-cols': group.columns.length }">
      <div class="mrt-ov-grid-row mrt-ov-grid-row--head">
        <div class="mrt-ov-station-col">Station</div>
        <div
          v-for="col in group.columns"
          :key="`type-${col.serviceNumber}`"
          class="mrt-ov-col-head"
          :class="{ 'mrt-ov-cell--highlight': !!col.highlightColor }"
          :style="overviewHighlightStyle(col.highlightColor)"
        >
          <img
            v-if="trainTypeIconUrl(iconUrls, col.iconKey)"
            class="mrt-ov-icon"
            :src="trainTypeIconUrl(iconUrls, col.iconKey)"
            :alt="col.trainTypeName"
            width="36"
            height="36"
          />
          <span>{{ col.trainTypeName }}</span>
        </div>
      </div>
      <div class="mrt-ov-grid-row mrt-ov-grid-row--head">
        <div class="mrt-ov-station-col mrt-ov-station-col--empty" aria-hidden="true" />
        <div
          v-for="col in group.columns"
          :key="`num-${col.serviceNumber}`"
          class="mrt-ov-col-head mrt-ov-col-head--number"
          :class="{ 'mrt-ov-cell--highlight': !!col.highlightColor }"
          :style="overviewHighlightStyle(col.highlightColor)"
        >
          {{ col.serviceNumber }}
          <span v-if="col.specialName" class="mrt-ov-special">{{ col.specialName }}</span>
        </div>
      </div>

      <div
        v-for="(row, ri) in group.rows"
        :key="ri"
        class="mrt-ov-grid-row"
        :class="overviewRowClass(row, ri)"
      >
        <div class="mrt-ov-station-col">{{ row.label }}</div>
        <template v-if="isTimeRow(row)">
          <div
            v-for="(cell, ci) in row.cells"
            :key="ci"
            class="mrt-ov-time-cell"
            :class="{ 'mrt-ov-cell--highlight': !!cell.highlightColor }"
            :style="overviewHighlightStyle(cell.highlightColor)"
          >
            {{ cell.text }}
            <span v-if="cell.specialName" class="mrt-ov-special">{{ cell.specialName }}</span>
          </div>
        </template>
        <template v-else-if="isTransferRow(row)">
          <div
            v-for="(cell, ci) in row.cells"
            :key="ci"
            class="mrt-ov-transfer-cell"
            :class="{ 'mrt-ov-transfer-cell--empty': !cell.vehicles.length }"
          >
            <div v-for="(v, vi) in cell.vehicles" :key="vi" class="mrt-ov-vehicle">
              <img
                v-if="trainTypeIconUrl(iconUrls, v.iconKey)"
                class="mrt-ov-icon"
                :src="trainTypeIconUrl(iconUrls, v.iconKey)"
                :alt="v.typeName"
                width="32"
                height="32"
              />
              <span class="mrt-ov-vehicle-type">{{ v.typeName }}</span>
              <span class="mrt-ov-vehicle-num">{{ v.serviceNumber }}</span>
              <span v-if="v.detail" class="mrt-ov-vehicle-detail">{{ v.detail }}</span>
            </div>
          </div>
        </template>
      </div>
    </div>
    </div>
  </section>
</template>
