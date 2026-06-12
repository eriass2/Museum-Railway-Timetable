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

.mrt-month-table {
  table-layout: fixed;
}

.mrt-month-table :deep(th),
.mrt-month-table :deep(td) {
  border: 1px solid var(--mrt-border-light);
  width: 14.285%;
  vertical-align: top;
  height: auto;
  min-height: var(--mrt-month-cell-min-height, 5rem);
  padding: 0;
  overflow: visible;
}

.mrt-month-table :deep(thead th) {
  background: var(--mrt-bg-lightest);
  font-weight: 700;
  font-size: 0.9rem;
  letter-spacing: 0.01em;
  padding: 0.5rem 0.2rem;
  text-align: center;
  vertical-align: middle;
}

.mrt-month-table__caption {
  caption-side: top;
  text-align: left;
  font-weight: 600;
  padding: var(--mrt-spacing-xs) 0;
}

.mrt-month-table :deep(.mrt-day-cell--inactive),
.mrt-month-table :deep(.mrt-day-cell--running) {
  padding: 0;
  vertical-align: stretch;
}

@media (max-width: 40rem) {
  .mrt-month-table {
    border-collapse: separate;
    border-spacing: 0;
  }

  .mrt-month-table__caption {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }

  .mrt-month-table :deep(th),
  .mrt-month-table :deep(td) {
    border-width: 0 1px 1px 0;
  }

  .mrt-month-table :deep(tr:first-child th) {
    border-top: 0;
  }

  .mrt-month-table :deep(th:last-child),
  .mrt-month-table :deep(td:last-child) {
    border-right: 0;
  }

  .mrt-month-table :deep(tr:last-child td) {
    border-bottom: 0;
  }

  .mrt-month-table :deep(thead th) {
    padding: 0.45rem 0.1rem;
    font-size: 0.78rem;
    font-weight: 700;
    background: var(--mrt-bg-white, #fff);
    border-bottom: 2px solid var(--mrt-border-light);
  }
}
</style>
