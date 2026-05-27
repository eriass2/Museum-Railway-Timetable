import { onMounted, ref, watch, type ComputedRef } from 'vue';
import { useMrtAjax } from '../../composables/useMrtAjax';
import type { WizardVueConfig } from '../../config/types';
import type { WizardStore } from '../store/createWizardStore';
import type { CalendarDayStatus } from '../types';
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
  const { loading, run } = useMrtAjax(config);
  const startOfWeek = Number(config.startOfWeek ?? 1);
  const daysMap = ref<Record<string, CalendarDayStatus>>({});
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
