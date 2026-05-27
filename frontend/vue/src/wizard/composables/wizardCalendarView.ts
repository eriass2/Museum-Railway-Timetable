import { computed, type ComputedRef, type Ref } from 'vue';
import type { WizardStore } from '../store/createWizardStore';
import type { CalendarDayStatus } from '../types';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import { cfgStringArray } from '../utils/wizardLabels';
import { calendarMonthTitle } from '../utils/wizardDate';
import { buildWizardCalendarGrid, orderedWeekdayHeaders } from '../utils/wizardCalendarGrid';
import { wizardCalendarDayAria } from './wizardCalendarLoad';

export function useWizardCalendarView(
  store: WizardStore,
  cfg: ComputedRef<WizardCfg>,
  daysMap: Ref<Record<string, CalendarDayStatus>>,
  startOfWeek: number,
) {
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

  function dayAria(ymd: string, status: CalendarDayStatus): string {
    return wizardCalendarDayAria(ymd, status, cfg);
  }

  return { monthTitle, weekdayHeaders, gridRows, dayAria };
}
