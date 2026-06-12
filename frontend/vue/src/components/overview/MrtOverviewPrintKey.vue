<script setup lang="ts">
import type { OverviewUiLabels } from '../../shared/overviewUiLabels';
import type { TimetablePrintKeyRow } from '../../types/timetableOverview';

defineProps<{
  rows: TimetablePrintKeyRow[];
  labels: OverviewUiLabels;
}>();
</script>

<template>
  <section class="mrt-ov-print-key" :aria-label="labels.printKeyTitle">
    <h4 class="mrt-ov-print-key-title">{{ labels.printKeyTitle }}</h4>
    <table class="mrt-ov-print-key-table">
      <thead>
        <tr>
          <th>{{ labels.printKeySymbolCol }}</th>
          <th>{{ labels.printKeyMeaningCol }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, i) in rows" :key="i">
          <td>{{ row.symbol }}</td>
          <td>{{ row.text }}</td>
        </tr>
      </tbody>
    </table>
    <p class="mrt-ov-print-key-note">{{ labels.printKeyNote }}</p>
  </section>
</template>

<style scoped>
.mrt-ov-print-key {
  margin-top: var(--mrt-spacing-lg, 1.5rem);
  padding: var(--mrt-spacing-md, 1rem) 1.25rem;
  background: var(--mrt-bg-lightest, #fafafa);
  border: 1px solid var(--mrt-border-default, #ddd);
}

.mrt-ov-print-key-title {
  margin: 0 0 0.75rem;
  font-size: 1rem;
  font-weight: 700;
}

.mrt-ov-print-key-table {
  width: 100%;
  border-collapse: collapse;
}

.mrt-ov-print-key-table th,
.mrt-ov-print-key-table td {
  border: 1px solid var(--mrt-border-default, #ccc);
  padding: 0.4rem 0.55rem;
  vertical-align: top;
}

.mrt-ov-print-key-table th {
  width: 30%;
  background: var(--mrt-wizard-surface, #fff);
  font-weight: 700;
}

.mrt-ov-print-key-note {
  margin: 0.75rem 0 0;
  font-size: var(--mrt-font-sm, 0.85rem);
  color: var(--mrt-color-neutral-600, #525252);
  font-style: italic;
}

@media (max-width: 40rem) {
  .mrt-ov-print-key {
    padding-inline: 0.85rem;
  }

  .mrt-ov-print-key-table thead {
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

  .mrt-ov-print-key-table tbody tr {
    display: block;
    margin-bottom: 0.65rem;
    border: 1px solid var(--mrt-border-default, #ccc);
    background: var(--mrt-wizard-surface, #fff);
  }

  .mrt-ov-print-key-table tbody tr:last-child {
    margin-bottom: 0;
  }

  .mrt-ov-print-key-table td {
    display: block;
    border: 0;
    padding: 0.45rem 0.65rem;
  }

  .mrt-ov-print-key-table td:first-child {
    padding-bottom: 0.25rem;
    font-weight: 700;
    background: var(--mrt-bg-lightest, #fafafa);
    border-bottom: 1px solid var(--mrt-border-default, #e8e8e8);
  }
}
</style>
