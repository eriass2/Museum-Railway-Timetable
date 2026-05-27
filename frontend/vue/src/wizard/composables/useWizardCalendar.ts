import { computed, onMounted, ref, watch, type ComputedRef } from 'vue';
import { useMrtAjax } from '../../composables/useMrtAjax';
import type { WizardVueConfig } from '../../config/types';
import type { WizardStore } from '../store/createWizardStore';
import type { CalendarDayStatus } from '../types';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import { cfgStringArray } from '../utils/wizardLabels';
import { calendarMonthTitle } from '../utils/wizardDate';
import { buildWizardCalendarGrid, orderedWeekdayHeaders } from '../utils/wizardCalendarGrid';
import {
  goWizardCalendarToday,
  initWizardCalendar,
  loadWizardCalendarMonth,
  pickWizardCalendarDate,
  shiftWizardCalendarMonth,
  wizardCalendarDayAria,
} from './wizardCalendarLoad';

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

  function dayAria(ymd: string, status: CalendarDayStatus): string {
    return wizardCalendarDayAria(ymd, status, cfg);
  }

  function onPickDate(ymd: string): void {
    pickWizardCalendarDate(store, ymd);
  }

  function shiftMonth(delta: number): void {
    shiftWizardCalendarMonth(store, cfg, daysMap, run, delta);
  }

  function goToday(): void {
    goWizardCalendarToday(store, cfg, daysMap, run);
  }

  onMounted(() => {
    initWizardCalendar(store, config, cfg, daysMap, run);
  });

  watch(
    () => store.step,
    (s) => {
      if (s === 'date' && store.calYear) {
        void loadWizardCalendarMonth(store, cfg, daysMap, run, store.calYear, store.calMonth);
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
