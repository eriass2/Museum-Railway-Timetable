import { onMounted, ref, watch, type ComputedRef } from 'vue';
import { useMrtRest } from '../../composables/useMrtRest';
import type { WizardVueConfig } from '../../config/types';
import type { WizardStore } from '../store/createWizardStore';
import type { CalendarDayInfo, CalendarDayStatus } from '../../shared/calendarDay';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import {
  goWizardCalendarToday,
  initWizardCalendar,
  loadWizardCalendarMonth,
  pickWizardCalendarDate,
  shiftWizardCalendarMonth,
} from './wizardCalendarLoad';
import { useWizardCalendarView } from './wizardCalendarView';

export function useWizardCalendar(
  store: WizardStore,
  config: WizardVueConfig,
  cfg: ComputedRef<WizardCfg>,
) {
  const { loading, run } = useMrtRest(config);
  const startOfWeek = Number(config.startOfWeek ?? 1);
  const daysMap = ref<Record<string, CalendarDayInfo | CalendarDayStatus>>({});
  const view = useWizardCalendarView(store, cfg, daysMap, startOfWeek);

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
    ...view,
    onPickDate: (ymd: string) => pickWizardCalendarDate(store, ymd),
    shiftMonth: (delta: number) => shiftWizardCalendarMonth(store, cfg, daysMap, run, delta),
    goToday: () => goWizardCalendarToday(store, cfg, daysMap, run),
  };
}
