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
    :class="{
      'mrt-month-table': variant === 'month',
      'mrt-calendar-grid__table--wizard': variant === 'wizard',
    }"
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

<style scoped>
.mrt-calendar-grid__table {
  width: 100%;
  border-collapse: collapse;
}

.mrt-calendar-grid__table--wizard {
  table-layout: fixed;
}

.mrt-calendar-grid__table--wizard :deep(th),
.mrt-calendar-grid__table--wizard :deep(td) {
  width: 14.285%;
  padding: 0.15rem 0.05rem;
  overflow: hidden;
  text-align: center;
}

.mrt-calendar-grid__table--wizard :deep(th) {
  padding: 0.4rem 0.25rem;
  color: var(--mrt-wizard-text);
  background: transparent;
  border-color: var(--mrt-color-neutral-200);
  font-size: 0.95rem;
  font-weight: 700;
}

.mrt-calendar-grid__table--wizard :deep(td) {
  height: auto;
  vertical-align: middle;
  border-color: var(--mrt-color-neutral-200);
}
</style>
