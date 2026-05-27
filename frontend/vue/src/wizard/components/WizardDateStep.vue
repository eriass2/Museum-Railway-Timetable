<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { useMrtAjax } from '../../composables/useMrtAjax';
import MrtStepShell from '../../components/MrtStepShell.vue';
import WizardCalendarGrid from './WizardCalendarGrid.vue';
import WizardCalendarLegend from './WizardCalendarLegend.vue';
import type { CalendarDayStatus } from '../types';
import { cfgStr } from '../utils/wizardLabels';
import {
  addCalendarMonths,
  calendarMonthTitle,
  formatYmdForDisplay,
  todayYearMonth,
} from '../utils/wizardDate';
import { buildWizardCalendarGrid, orderedWeekdayHeaders } from '../utils/wizardCalendarGrid';

const { store, cfg, config } = useWizardContext();
const { loading, run } = useMrtAjax(config);

const startOfWeek = Number(config.startOfWeek ?? 1);
const daysMap = ref<Record<string, CalendarDayStatus>>({});

const monthNames = computed(() => cfg.value.monthNames as string[] | undefined);
const weekdayAbbrev = computed(() => (cfg.value.weekdayAbbrev as string[]) || []);

const monthTitle = computed(() =>
  calendarMonthTitle(store.calYear, store.calMonth, monthNames.value),
);

const weekdayHeaders = computed(() => orderedWeekdayHeaders(weekdayAbbrev.value, startOfWeek));

const gridRows = computed(() =>
  buildWizardCalendarGrid(store.calYear, store.calMonth, startOfWeek, daysMap.value),
);

const stepTitle = computed(() => cfgStr(cfg, 'stepDate', 'Välj datum'));

async function loadCalendar(year: number, month: number): Promise<void> {
  store.calYear = year;
  store.calMonth = month;
  if (store.debugCalendarDays) {
    daysMap.value = store.debugCalendarDays;
    return;
  }
  const res = await run<{ year: number; month: number; days: Record<string, CalendarDayStatus> }>(
    'mrt_journey_calendar_month',
    { from_station: store.fromId, to_station: store.toId, year, month },
  );
  if (!res.success || !res.data) {
    store.showError(cfgStr(cfg, 'errorGeneric', 'Something went wrong.'));
    return;
  }
  daysMap.value = res.data.days || {};
}

function dayAria(ymd: string, status: CalendarDayStatus): string {
  const human = formatYmdForDisplay(ymd, monthNames.value);
  if (status === 'ok') {
    return cfgStr(cfg, 'dayDateOk', human).replace('%s', human);
  }
  if (status === 'traffic_no_match') {
    return cfgStr(cfg, 'dayDateTraffic', human).replace('%s', human);
  }
  return cfgStr(cfg, 'dayDateNone', human).replace('%s', human);
}

function onPickDate(ymd: string): void {
  store.dateYmd = ymd;
  store.goTo('outbound');
}

function onBack(): void {
  store.dateYmd = '';
  store.goTo('route');
}

function shiftMonth(delta: number): void {
  const cm = addCalendarMonths(store.calYear, store.calMonth, delta);
  void loadCalendar(cm.year, cm.month);
}

function goToday(): void {
  const now = todayYearMonth();
  void loadCalendar(now.year, now.month);
}

onMounted(() => {
  if (!store.calYear) {
    const now = todayYearMonth();
    void loadCalendar(now.year, now.month);
  } else {
    void loadCalendar(store.calYear, store.calMonth);
  }
});

watch(
  () => store.step,
  (s) => {
    if (s === 'date' && store.calYear) {
      void loadCalendar(store.calYear, store.calMonth);
    }
  },
);
</script>

<template>
  <div class="mrt-journey-wizard__panel mrt-journey-wizard__panel--active" role="region">
    <MrtStepShell :cfg="cfg" :context-line="store.contextLine" :title="stepTitle" @back="onBack" />
    <div class="mrt-journey-wizard__calendar-card">
      <div class="mrt-journey-wizard__calendar-nav">
        <button
          type="button"
          class="mrt-journey-wizard__cal-prev"
          aria-label="Previous month"
          @click="shiftMonth(-1)"
        >
          ‹
        </button>
        <span class="mrt-journey-wizard__cal-title" aria-live="polite">{{ monthTitle }}</span>
        <button
          type="button"
          class="mrt-journey-wizard__cal-next"
          aria-label="Next month"
          @click="shiftMonth(1)"
        >
          ›
        </button>
        <button type="button" class="mrt-journey-wizard__cal-today" @click="goToday">
          {{ cfgStr(cfg, 'thisMonth', 'Denna månad') }}
        </button>
      </div>
      <WizardCalendarGrid
        :loading="loading"
        :weekday-headers="weekdayHeaders"
        :grid-rows="gridRows"
        :selected-ymd="store.dateYmd"
        :grid-label="cfgStr(cfg, 'calendarGridLabel', '')"
        :loading-label="cfgStr(cfg, 'loading', 'Loading...')"
        :day-aria="dayAria"
        @pick="onPickDate"
      />
      <WizardCalendarLegend :cfg="cfg" />
    </div>
  </div>
</template>
