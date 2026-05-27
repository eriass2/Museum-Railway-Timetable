import { computed, onMounted, ref, watch, type ComputedRef } from 'vue';
import { useMrtAjax } from '../../composables/useMrtAjax';
import type { WizardVueConfig } from '../../config/types';
import type { WizardStore } from '../store/createWizardStore';
import type { CalendarDayStatus } from '../types';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import { cfgStr, cfgStringArray } from '../utils/wizardLabels';
import {
  addCalendarMonths,
  calendarMonthTitle,
  formatYmdForDisplay,
  todayYearMonth,
} from '../utils/wizardDate';
import { buildWizardCalendarGrid, orderedWeekdayHeaders } from '../utils/wizardCalendarGrid';

export function useWizardCalendar(
  store: WizardStore,
  config: WizardVueConfig,
  cfg: ComputedRef<WizardCfg>,
) {
  const { loading, run } = useMrtAjax(config);
  const startOfWeek = Number(config.startOfWeek ?? 1);
  const daysMap = ref<Record<string, CalendarDayStatus>>({});

  const monthNames = computed(() => cfgStringArray(cfg.value, 'monthNames'));
  const weekdayAbbrev = computed(() => cfgStringArray(cfg.value, 'weekdayAbbrev'));

  const monthTitle = computed(() =>
    calendarMonthTitle(store.calYear, store.calMonth, monthNames.value),
  );

  const weekdayHeaders = computed(() =>
    orderedWeekdayHeaders(weekdayAbbrev.value, startOfWeek),
  );

  const gridRows = computed(() =>
    buildWizardCalendarGrid(store.calYear, store.calMonth, startOfWeek, daysMap.value),
  );

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
      store.showError(cfgStr(cfg, 'errorGeneric', 'Något gick fel.'));
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

  return {
    loading,
    monthTitle,
    weekdayHeaders,
    gridRows,
    dayAria,
    onPickDate,
    shiftMonth,
    goToday,
  };
}
