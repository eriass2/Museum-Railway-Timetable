<script setup lang="ts" generic="T">
defineProps<{
  variant: 'wizard' | 'month';
  weekdayHeaders: string[];
  rows: T[][];
  gridLabel?: string;
  caption?: string;
  cellClass?: (cell: T) => string | undefined;
}>();
</script>

<template>
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
</template>
