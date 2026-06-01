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
