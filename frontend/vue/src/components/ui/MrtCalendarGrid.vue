<script setup lang="ts" generic="T">
import MrtAsyncState from './MrtAsyncState.vue';

withDefaults(
  defineProps<{
    variant: 'wizard' | 'month';
    weekdayHeaders: string[];
    rows: T[][];
    gridLabel?: string;
    caption?: string;
    loading?: boolean;
    loadingLabel?: string;
    cellClass?: (cell: T) => string | undefined;
  }>(),
  {
    gridLabel: '',
    caption: '',
    loadingLabel: '',
  },
);
</script>

<template>
  <div
    class="mrt-calendar-grid"
    :class="`mrt-calendar-grid--${variant}`"
    role="region"
    :aria-busy="loading === true ? true : undefined"
  >
    <MrtAsyncState
      v-if="loading !== undefined"
      :loading="loading"
      :loading-text="loadingLabel"
    >
      <table
        class="mrt-calendar-grid__table"
        :class="{ 'mrt-month-table': variant === 'month' }"
        role="grid"
        :aria-label="gridLabel || undefined"
      >
        <caption v-if="caption" class="mrt-calendar-grid__caption mrt-month-table__caption">
          {{ caption }}
        </caption>
        <thead>
          <tr>
            <th v-for="(h, i) in weekdayHeaders" :key="i" scope="col">{{ h }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, ri) in rows" :key="ri">
            <td
              v-for="(cell, ci) in row"
              :key="`${ri}-${ci}`"
              :class="cellClass?.(cell)"
            >
              <slot name="cell" :cell="cell" :row-index="ri" :col-index="ci" />
            </td>
          </tr>
        </tbody>
      </table>
    </MrtAsyncState>
    <table
      v-else
      class="mrt-calendar-grid__table"
      :class="{ 'mrt-month-table': variant === 'month' }"
      role="grid"
      :aria-label="gridLabel || undefined"
    >
      <caption v-if="caption" class="mrt-calendar-grid__caption mrt-month-table__caption">
        {{ caption }}
      </caption>
      <thead>
        <tr>
          <th v-for="(h, i) in weekdayHeaders" :key="i" scope="col">{{ h }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, ri) in rows" :key="ri">
          <td
            v-for="(cell, ci) in row"
            :key="`${ri}-${ci}`"
            :class="cellClass?.(cell)"
          >
            <slot name="cell" :cell="cell" :row-index="ri" :col-index="ci" />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
