<script setup lang="ts">
import type { TimetablePrintKeyRow } from '../../types/timetableOverview';

defineProps<{
  rows: TimetablePrintKeyRow[];
}>();
</script>

<template>
  <section class="mrt-ov-print-key" aria-label="Förklaringar">
    <h4 class="mrt-ov-print-key-title">Förklaringar</h4>
    <table class="mrt-ov-print-key-table">
      <thead>
        <tr>
          <th>Tecken</th>
          <th>Betydelse</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, i) in rows" :key="i">
          <td>{{ row.symbol }}</td>
          <td>{{ row.text }}</td>
        </tr>
      </tbody>
    </table>
    <p class="mrt-ov-print-key-note">Med reservation för ändring av tågtyp.</p>
  </section>
</template>
