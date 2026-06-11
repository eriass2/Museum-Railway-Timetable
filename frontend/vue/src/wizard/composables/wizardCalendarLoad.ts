import type { ComputedRef, Ref } from 'vue';
import type { WizardVueConfig } from '../../config/types';
import type { WizardStore } from '../store/createWizardStore';
import type { CalendarDayInfo, CalendarDayStatus } from '../../shared/calendarDay';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import { calendarMonthParams } from '../cache/cacheKeys';
import type { WizardResourceCache } from '../cache/resourceCache';
import { cfgStr, cfgStringArray } from '../utils/wizardLabels';
import {
  addCalendarMonths,
  formatYmdForDisplay,
  todayYearMonth,
} from '../utils/wizardDate';

import type { MrtRestRequestInit, MrtRestResponse } from '../../api/mrtRest';

type RestRun = <T>(init: MrtRestRequestInit) => Promise<MrtRestResponse<T>>;

type CalendarResponse = {
  year: number;
  month: number;
  days: Record<string, CalendarDayInfo>;
};

async function fetchCalendarMonth(
  store: WizardStore,
  run: RestRun,
  year: number,
  month: number,
  tripType: string,
): Promise<Record<string, CalendarDayInfo> | null> {
  const res = await run<CalendarResponse>({
    method: 'POST',
    path: 'journey/calendar',
    body: {
      from_station: store.fromId,
      to_station: store.toId,
      year,
      month,
      trip_type: tripType,
    },
  });
  if (!res.success || !res.data?.days) {
    return null;
  }
  return res.data.days;
}

export async function loadWizardCalendarMonth(
  store: WizardStore,
  cfg: ComputedRef<WizardCfg>,
  daysMap: Ref<Record<string, CalendarDayInfo | CalendarDayStatus>>,
  run: RestRun,
  resourceCache: WizardResourceCache,
  year: number,
  month: number,
): Promise<void> {
  store.calYear = year;
  store.calMonth = month;
  if (store.debugCalendarDays) {
    daysMap.value = store.debugCalendarDays;
    return;
  }

  const tripType = store.tripType;
  const params = calendarMonthParams(store.fromId, store.toId, tripType, year, month);
  const days = await resourceCache.load(
    {
      resource: 'calendar.month',
      params,
      request: () => fetchCalendarMonth(store, run, year, month, tripType),
    },
    { priority: 'user' },
  );

  if (!days) {
    store.showError(cfgStr(cfg, 'errorGeneric', 'Något gick fel. Försök igen.'));
    return;
  }

  daysMap.value = days;
  resourceCache.prefetchRelated('calendar.month', params, (spec) => {
    const p = spec.params;
    return fetchCalendarMonth(
      store,
      run,
      Number(p.year),
      Number(p.month),
      String(p.trip_type),
    );
  });
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
  daysMap: Ref<Record<string, CalendarDayInfo | CalendarDayStatus>>,
  run: RestRun,
  resourceCache: WizardResourceCache,
  delta: number,
): void {
  const cm = addCalendarMonths(store.calYear, store.calMonth, delta);
  void loadWizardCalendarMonth(store, cfg, daysMap, run, resourceCache, cm.year, cm.month);
}

export function goWizardCalendarToday(
  store: WizardStore,
  cfg: ComputedRef<WizardCfg>,
  daysMap: Ref<Record<string, CalendarDayInfo | CalendarDayStatus>>,
  run: RestRun,
  resourceCache: WizardResourceCache,
): void {
  const now = todayYearMonth();
  void loadWizardCalendarMonth(store, cfg, daysMap, run, resourceCache, now.year, now.month);
}

export function initWizardCalendar(
  store: WizardStore,
  config: WizardVueConfig,
  cfg: ComputedRef<WizardCfg>,
  daysMap: Ref<Record<string, CalendarDayInfo | CalendarDayStatus>>,
  run: RestRun,
  resourceCache: WizardResourceCache,
): void {
  if (!store.calYear) {
    const now = todayYearMonth();
    void loadWizardCalendarMonth(store, cfg, daysMap, run, resourceCache, now.year, now.month);
    return;
  }
  void loadWizardCalendarMonth(store, cfg, daysMap, run, resourceCache, store.calYear, store.calMonth);
}
