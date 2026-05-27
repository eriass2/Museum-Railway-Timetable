<script setup lang="ts">
import type { CalendarDayStatus } from '../types';
import type { WizardCalCell } from '../utils/wizardCalendarGrid';

defineProps<{
  loading: boolean;
  weekdayHeaders: string[];
  gridRows: WizardCalCell[][];
  selectedYmd: string;
  gridLabel: string;
  loadingLabel: string;
  dayAria: (ymd: string, status: CalendarDayStatus) => string;
}>();

const emit = defineEmits<{ pick: [ymd: string] }>();
</script>

<template>
  <div class="mrt-journey-wizard__calendar" role="region" :aria-busy="loading">
    <p v-if="loading" class="mrt-empty">{{ loadingLabel }}</p>
    <table v-else role="grid" :aria-label="gridLabel">
      <thead>
        <tr>
          <th v-for="(h, i) in weekdayHeaders" :key="i" scope="col">{{ h }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, ri) in gridRows" :key="ri">
          <td v-for="(cell, ci) in row" :key="`${ri}-${ci}`">
            <template v-if="cell.kind === 'pad'" />
            <button
              v-else-if="cell.status === 'ok'"
              type="button"
              class="mrt-journey-wizard__day mrt-journey-wizard__day--ok"
              :class="{ 'is-selected': selectedYmd === cell.ymd }"
              :aria-label="dayAria(cell.ymd, cell.status)"
              :aria-pressed="selectedYmd === cell.ymd"
              @click="emit('pick', cell.ymd)"
            >
              {{ cell.day }}
            </button>
            <button
              v-else-if="cell.status === 'traffic_no_match'"
              type="button"
              class="mrt-journey-wizard__day mrt-journey-wizard__day--traffic"
              disabled
              :aria-label="dayAria(cell.ymd, cell.status)"
            >
              {{ cell.day }}
            </button>
            <button
              v-else
              type="button"
              class="mrt-journey-wizard__day mrt-journey-wizard__day--none"
              disabled
              :aria-label="dayAria(cell.ymd, cell.status)"
            >
              {{ cell.day }}
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
