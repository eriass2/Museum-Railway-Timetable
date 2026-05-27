<script setup lang="ts">
import { computed, inject, onMounted, ref, watch } from 'vue';
import { mrtPost } from '../../api/mrtApi';
import { wizardKey } from '../injection';
import type { CalendarDayStatus } from '../types';
import { cfgStr } from '../utils/wizardLabels';
import {
  addCalendarMonths,
  calendarMonthTitle,
  formatYmdForDisplay,
  todayYearMonth,
} from '../utils/wizardDate';
import { buildWizardCalendarGrid, orderedWeekdayHeaders } from '../utils/wizardCalendarGrid';

const wizard = inject(wizardKey);
if (!wizard) {
  throw new Error('WizardDateStep requires wizard context');
}

const startOfWeek = Number(wizard.config.startOfWeek ?? 1);
const loading = ref(false);
const daysMap = ref<Record<string, CalendarDayStatus>>({});

const monthTitle = computed(() =>
  calendarMonthTitle(wizard.calYear.value, wizard.calMonth.value, wizard.cfg.monthNames as string[]),
);

const weekdayHeaders = computed(() =>
  orderedWeekdayHeaders((wizard.cfg.weekdayAbbrev as string[]) || [], startOfWeek),
);

const gridRows = computed(() =>
  buildWizardCalendarGrid(wizard.calYear.value, wizard.calMonth.value, startOfWeek, daysMap.value),
);

async function loadCalendar(year: number, month: number): Promise<void> {
  wizard.calYear.value = year;
  wizard.calMonth.value = month;
  loading.value = true;
  const res = await mrtPost<{ year: number; month: number; days: Record<string, CalendarDayStatus> }>(
    wizard.config,
    'mrt_journey_calendar_month',
    { from_station: wizard.fromId.value, to_station: wizard.toId.value, year, month },
  );
  loading.value = false;
  if (!res.success || !res.data) {
    wizard.showError(cfgStr(wizard.cfg, 'errorGeneric', 'Something went wrong.'));
    return;
  }
  daysMap.value = res.data.days || {};
}

function dayAria(ymd: string, status: CalendarDayStatus): string {
  const human = formatYmdForDisplay(ymd, wizard.cfg.monthNames as string[] | undefined);
  if (status === 'ok') {
    return cfgStr(wizard.cfg, 'dayDateOk', human).replace('%s', human);
  }
  if (status === 'traffic_no_match') {
    return cfgStr(wizard.cfg, 'dayDateTraffic', human).replace('%s', human);
  }
  return cfgStr(wizard.cfg, 'dayDateNone', human).replace('%s', human);
}

function onPickDate(ymd: string): void {
  wizard.dateYmd.value = ymd;
  wizard.goTo('outbound');
}

function onBack(): void {
  wizard.dateYmd.value = '';
  wizard.goTo('route');
}

function shiftMonth(delta: number): void {
  const cm = addCalendarMonths(wizard.calYear.value, wizard.calMonth.value, delta);
  void loadCalendar(cm.year, cm.month);
}

function goToday(): void {
  const now = todayYearMonth();
  void loadCalendar(now.year, now.month);
}

onMounted(() => {
  if (!wizard.calYear.value) {
    const now = todayYearMonth();
    void loadCalendar(now.year, now.month);
  } else {
    void loadCalendar(wizard.calYear.value, wizard.calMonth.value);
  }
});

watch(
  () => wizard.step.value,
  (s) => {
    if (s === 'date' && wizard.calYear.value) {
      void loadCalendar(wizard.calYear.value, wizard.calMonth.value);
    }
  },
);
</script>

<template>
  <div class="mrt-jw-panel mrt-journey-wizard__panel mrt-jw-panel--active mrt-journey-wizard__panel--active" role="region">
    <header class="mrt-jw-step-head mrt-journey-wizard__step-head">
      <button type="button" class="mrt-jw-btn mrt-jw-btn--back mrt-journey-wizard__back" @click="onBack">
        {{ cfgStr(wizard.cfg, 'back', '← Tillbaka') }}
      </button>
      <p class="mrt-jw-step-head__context mrt-journey-wizard__context">{{ wizard.contextLine }}</p>
    </header>
    <h3 class="mrt-jw-typo mrt-jw-typo--step-title mrt-journey-wizard__step-title">
      {{ cfgStr(wizard.cfg, 'stepDate', 'Välj datum') }}
    </h3>
    <div class="mrt-jw-card mrt-jw-card--calendar mrt-journey-wizard__calendar-card">
      <div class="mrt-jw-calendar__nav mrt-journey-wizard__calendar-nav">
        <button
          type="button"
          class="mrt-jw-btn mrt-jw-btn--cal-nav mrt-journey-wizard__cal-prev"
          aria-label="Previous month"
          @click="shiftMonth(-1)"
        >
          ‹
        </button>
        <span class="mrt-jw-typo mrt-jw-typo--cal-title mrt-journey-wizard__cal-title" aria-live="polite">{{ monthTitle }}</span>
        <button
          type="button"
          class="mrt-jw-btn mrt-jw-btn--cal-nav mrt-journey-wizard__cal-next"
          aria-label="Next month"
          @click="shiftMonth(1)"
        >
          ›
        </button>
        <button
          type="button"
          class="mrt-jw-btn mrt-jw-btn--cal-today mrt-journey-wizard__cal-today"
          @click="goToday"
        >
          {{ cfgStr(wizard.cfg, 'thisMonth', 'Denna månad') }}
        </button>
      </div>
      <div class="mrt-jw-calendar__grid mrt-journey-wizard__calendar" role="region" :aria-busy="loading">
        <p v-if="loading" class="mrt-empty">{{ cfgStr(wizard.cfg, 'loading', 'Loading...') }}</p>
        <table v-else role="grid" :aria-label="cfgStr(wizard.cfg, 'calendarGridLabel', '')">
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
                  class="mrt-jw-btn mrt-jw-btn--day mrt-jw-btn--day-ok mrt-journey-wizard__day mrt-journey-wizard__day--ok"
                  :class="{ 'is-selected': wizard.dateYmd === cell.ymd }"
                  :aria-label="dayAria(cell.ymd, cell.status)"
                  :aria-pressed="wizard.dateYmd === cell.ymd"
                  @click="onPickDate(cell.ymd)"
                >
                  {{ cell.day }}
                </button>
                <button
                  v-else-if="cell.status === 'traffic_no_match'"
                  type="button"
                  class="mrt-jw-btn mrt-jw-btn--day mrt-jw-btn--day-traffic mrt-journey-wizard__day mrt-journey-wizard__day--traffic"
                  disabled
                  :aria-label="dayAria(cell.ymd, cell.status)"
                >
                  {{ cell.day }}
                </button>
                <button
                  v-else
                  type="button"
                  class="mrt-jw-btn mrt-jw-btn--day mrt-jw-btn--day-none mrt-journey-wizard__day mrt-journey-wizard__day--none"
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
      <ul class="mrt-jw-calendar__legend mrt-journey-wizard__legend">
        <li>
          <span class="mrt-jw-calendar__swatch mrt-jw-calendar__swatch--ok mrt-journey-wizard__swatch mrt-journey-wizard__swatch--ok" aria-hidden="true" />
          {{ cfgStr(wizard.cfg, 'legendOk', '') }}
        </li>
        <li>
          <span class="mrt-jw-calendar__swatch mrt-jw-calendar__swatch--traffic mrt-journey-wizard__swatch mrt-journey-wizard__swatch--traffic" aria-hidden="true" />
          {{ cfgStr(wizard.cfg, 'legendTraffic', '') }}
        </li>
        <li>
          <span class="mrt-jw-calendar__swatch mrt-jw-calendar__swatch--none mrt-journey-wizard__swatch mrt-journey-wizard__swatch--none" aria-hidden="true" />
          {{ cfgStr(wizard.cfg, 'legendNone', '') }}
        </li>
      </ul>
    </div>
  </div>
</template>
