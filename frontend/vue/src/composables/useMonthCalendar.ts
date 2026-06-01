import { ref } from 'vue';
import type { MonthLegendType, MonthVueConfig, MonthDayMeta } from '../config/types';
import { useMrtRest } from './useMrtRest';
import { resolveMrtString } from '../utils/mrtStrings';
import { syncMonthCalendarQuery } from '../utils/monthCalendarQuery';
import { addCalendarMonths } from '../wizard/utils/wizardDate';

export type MonthCalendarPayload = {
  year: number;
  month: number;
  daysInMonth: number;
  weekdayFirst: number;
  weekdayFirstSunday: number;
  monthTitle: string;
  monthAriaLabel: string;
  tableCaption: string;
  dates: Record<number, MonthDayMeta>;
  legendTimetableTypes: MonthLegendType[];
};

export function useMonthCalendar(config: MonthVueConfig) {
  const year = ref(Number(config.year) || new Date().getFullYear());
  const month = ref(Number(config.month) || new Date().getMonth() + 1);
  const daysInMonth = ref(Number(config.daysInMonth) || 0);
  const weekdayFirst = ref(Number(config.weekdayFirst) || 1);
  const weekdayFirstSunday = ref(Number(config.weekdayFirstSunday) || 0);
  const monthTitle = ref(config.monthTitle || '');
  const monthAriaLabel = ref(config.monthAriaLabel || '');
  const tableCaption = ref(config.tableCaption || '');
  const dates = ref<Record<number, MonthDayMeta>>(config.dates || {});
  const legendTimetableTypes = ref<MonthLegendType[]>(config.legendTimetableTypes || []);

  const { loading, error, run, clearError } = useMrtRest(config);

  function applyPayload(payload: MonthCalendarPayload): void {
    year.value = payload.year;
    month.value = payload.month;
    daysInMonth.value = payload.daysInMonth;
    weekdayFirst.value = payload.weekdayFirst;
    weekdayFirstSunday.value = payload.weekdayFirstSunday;
    monthTitle.value = payload.monthTitle;
    monthAriaLabel.value = payload.monthAriaLabel;
    tableCaption.value = payload.tableCaption;
    dates.value = payload.dates;
    legendTimetableTypes.value = payload.legendTimetableTypes;
    syncMonthCalendarQuery(payload.year, payload.month);
  }

  async function loadMonth(targetYear: number, targetMonth: number): Promise<boolean> {
    clearError();
    const atts = config.atts || {};
    const res = await run<MonthCalendarPayload>('mrt_get_timetable_month', {
      year: targetYear,
      month: targetMonth,
      train_type: String(atts.train_type || ''),
      service: String(atts.service || ''),
      start_monday: atts.start_monday ? 1 : 0,
    });
    if (!res.success || !res.data) {
      error.value = res.message || resolveMrtString(config, 'errorLoading', 'Kunde inte ladda kalendern.');
      return false;
    }
    applyPayload(res.data);
    return true;
  }

  async function shiftMonth(delta: number): Promise<boolean> {
    const next = addCalendarMonths(year.value, month.value, delta);
    return loadMonth(next.year, next.month);
  }

  return {
    year,
    month,
    daysInMonth,
    weekdayFirst,
    weekdayFirstSunday,
    monthTitle,
    monthAriaLabel,
    tableCaption,
    dates,
    legendTimetableTypes,
    loading,
    error,
    loadMonth,
    shiftMonth,
  };
}
