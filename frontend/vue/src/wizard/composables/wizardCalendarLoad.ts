import type { ComputedRef, Ref } from 'vue';
import type { WizardVueConfig } from '../../config/types';
import type { WizardStore } from '../store/createWizardStore';
import type { TripType } from '../types';
import type { CalendarDayInfo, CalendarDayStatus } from '../../shared/calendarDay';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import { cfgStr, cfgStringArray } from '../utils/wizardLabels';
import {
  addCalendarMonths,
  formatYmdForDisplay,
  todayYearMonth,
} from '../utils/wizardDate';
import {
  clearWizardCalendarCacheInFlight,
  getWizardCalendarCache,
  isWizardCalendarCacheInFlight,
  markWizardCalendarCacheInFlight,
  setWizardCalendarCache,
  wizardCalendarCacheKey,
} from '../utils/wizardCalendarCache';

import type { MrtRestRequestInit, MrtRestResponse } from '../../api/mrtRest';

type RestRun = <T>(init: MrtRestRequestInit) => Promise<MrtRestResponse<T>>;

function otherWizardTripType(tripType: string): TripType {
  return tripType === 'return' ? 'single' : 'return';
}

/** Warm client + server cache for the other trip type (single ↔ return). */
async function prefetchWizardCalendarMonth(
  store: WizardStore,
  run: RestRun,
  year: number,
  month: number,
): Promise<void> {
  if (store.debugCalendarDays || store.fromId <= 0 || store.toId <= 0) {
    return;
  }

  const tripType = otherWizardTripType(store.tripType);
  const cacheKey = wizardCalendarCacheKey(store.fromId, store.toId, tripType, year, month);
  if (getWizardCalendarCache(cacheKey) || isWizardCalendarCacheInFlight(cacheKey)) {
    return;
  }

  markWizardCalendarCacheInFlight(cacheKey);
  try {
    const res = await run<{ year: number; month: number; days: Record<string, CalendarDayInfo> }>({
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
    if (res.success && res.data?.days) {
      setWizardCalendarCache(cacheKey, res.data.days);
    }
  } finally {
    clearWizardCalendarCacheInFlight(cacheKey);
  }
}

function scheduleWizardCalendarPrefetch(
  store: WizardStore,
  run: RestRun,
  year: number,
  month: number,
): void {
  void prefetchWizardCalendarMonth(store, run, year, month);
}

export async function loadWizardCalendarMonth(
  store: WizardStore,
  cfg: ComputedRef<WizardCfg>,
  daysMap: Ref<Record<string, CalendarDayInfo | CalendarDayStatus>>,
  run: RestRun,
  year: number,
  month: number,
): Promise<void> {
  store.calYear = year;
  store.calMonth = month;
  if (store.debugCalendarDays) {
    daysMap.value = store.debugCalendarDays;
    return;
  }

  const cacheKey = wizardCalendarCacheKey(
    store.fromId,
    store.toId,
    store.tripType,
    year,
    month,
  );
  const cached = getWizardCalendarCache(cacheKey);
  if (cached) {
    daysMap.value = cached;
    scheduleWizardCalendarPrefetch(store, run, year, month);
    return;
  }

  const res = await run<{ year: number; month: number; days: Record<string, CalendarDayInfo> }>({
    method: 'POST',
    path: 'journey/calendar',
    body: {
      from_station: store.fromId,
      to_station: store.toId,
      year,
      month,
      trip_type: store.tripType,
    },
  });
  if (!res.success || !res.data) {
    store.showError(cfgStr(cfg, 'errorGeneric', 'Något gick fel. Försök igen.'));
    return;
  }
  const days = res.data.days || {};
  daysMap.value = days;
  setWizardCalendarCache(cacheKey, days);
  scheduleWizardCalendarPrefetch(store, run, year, month);
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
  delta: number,
): void {
  const cm = addCalendarMonths(store.calYear, store.calMonth, delta);
  void loadWizardCalendarMonth(store, cfg, daysMap, run, cm.year, cm.month);
}

export function goWizardCalendarToday(
  store: WizardStore,
  cfg: ComputedRef<WizardCfg>,
  daysMap: Ref<Record<string, CalendarDayInfo | CalendarDayStatus>>,
  run: RestRun,
): void {
  const now = todayYearMonth();
  void loadWizardCalendarMonth(store, cfg, daysMap, run, now.year, now.month);
}

export function initWizardCalendar(
  store: WizardStore,
  config: WizardVueConfig,
  cfg: ComputedRef<WizardCfg>,
  daysMap: Ref<Record<string, CalendarDayInfo | CalendarDayStatus>>,
  run: RestRun,
): void {
  if (!store.calYear) {
    const now = todayYearMonth();
    void loadWizardCalendarMonth(store, cfg, daysMap, run, now.year, now.month);
    return;
  }
  void loadWizardCalendarMonth(store, cfg, daysMap, run, store.calYear, store.calMonth);
}
