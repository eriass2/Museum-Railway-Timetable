import type { ComputedRef, Ref } from 'vue';
import type { WizardVueConfig } from '../../config/types';
import type { WizardStore } from '../store/createWizardStore';
import type { CalendarDayStatus } from '../types';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import { cfgStr, cfgStringArray } from '../utils/wizardLabels';
import {
  addCalendarMonths,
  formatYmdForDisplay,
  todayYearMonth,
} from '../utils/wizardDate';

type AjaxRun = <T>(
  action: string,
  data?: Record<string, string | number>,
) => Promise<{ success: boolean; data?: T; message?: string }>;

export async function loadWizardCalendarMonth(
  store: WizardStore,
  cfg: ComputedRef<WizardCfg>,
  daysMap: Ref<Record<string, CalendarDayStatus>>,
  run: AjaxRun,
  year: number,
  month: number,
): Promise<void> {
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

export function wizardCalendarDayAria(
  ymd: string,
  status: CalendarDayStatus,
  cfg: ComputedRef<WizardCfg>,
): string {
  const monthNames = cfgStringArray(cfg.value, 'monthNames');
  const human = formatYmdForDisplay(ymd, monthNames);
  if (status === 'ok') {
    return cfgStr(cfg, 'dayDateOk', human).replace('%s', human);
  }
  if (status === 'traffic_no_match') {
    return cfgStr(cfg, 'dayDateTraffic', human).replace('%s', human);
  }
  return cfgStr(cfg, 'dayDateNone', human).replace('%s', human);
}

export function pickWizardCalendarDate(store: WizardStore, ymd: string): void {
  store.dateYmd = ymd;
  store.goTo('outbound');
}

export function shiftWizardCalendarMonth(
  store: WizardStore,
  cfg: ComputedRef<WizardCfg>,
  daysMap: Ref<Record<string, CalendarDayStatus>>,
  run: AjaxRun,
  delta: number,
): void {
  const cm = addCalendarMonths(store.calYear, store.calMonth, delta);
  void loadWizardCalendarMonth(store, cfg, daysMap, run, cm.year, cm.month);
}

export function goWizardCalendarToday(
  store: WizardStore,
  cfg: ComputedRef<WizardCfg>,
  daysMap: Ref<Record<string, CalendarDayStatus>>,
  run: AjaxRun,
): void {
  const now = todayYearMonth();
  void loadWizardCalendarMonth(store, cfg, daysMap, run, now.year, now.month);
}

export function initWizardCalendar(
  store: WizardStore,
  config: WizardVueConfig,
  cfg: ComputedRef<WizardCfg>,
  daysMap: Ref<Record<string, CalendarDayStatus>>,
  run: AjaxRun,
): void {
  if (!store.calYear) {
    const now = todayYearMonth();
    void loadWizardCalendarMonth(store, cfg, daysMap, run, now.year, now.month);
    return;
  }
  void loadWizardCalendarMonth(store, cfg, daysMap, run, store.calYear, store.calMonth);
}
