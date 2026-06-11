import type { CalendarDayInfo, CalendarDayStatus } from '../../shared/calendarDay';

type CalendarDays = Record<string, CalendarDayInfo | CalendarDayStatus>;

const cache = new Map<string, CalendarDays>();

/** Keys currently being fetched (primary or prefetch). */
const inFlight = new Set<string>();

export function wizardCalendarCacheKey(
  fromId: number,
  toId: number,
  tripType: string,
  year: number,
  month: number,
): string {
  return `${fromId}|${toId}|${tripType}|${year}|${month}`;
}

export function getWizardCalendarCache(key: string): CalendarDays | undefined {
  return cache.get(key);
}

export function setWizardCalendarCache(key: string, days: CalendarDays): void {
  cache.set(key, days);
}

export function clearWizardCalendarCache(): void {
  cache.clear();
  inFlight.clear();
}

export function isWizardCalendarCacheInFlight(key: string): boolean {
  return inFlight.has(key);
}

export function markWizardCalendarCacheInFlight(key: string): void {
  inFlight.add(key);
}

export function clearWizardCalendarCacheInFlight(key: string): void {
  inFlight.delete(key);
}
