import type { CacheParams, CacheResource } from './cacheKeys';
import { calendarMonthParams } from './cacheKeys';

export type PrefetchSpec = {
  resource: CacheResource;
  params: CacheParams;
};

function otherTripType(tripType: string): string {
  return tripType === 'return' ? 'single' : 'return';
}

function shiftMonth(year: number, month: number, delta: number): { year: number; month: number } {
  let y = year;
  let m = month + delta;
  while (m < 1) {
    m += 12;
    y -= 1;
  }
  while (m > 12) {
    m -= 12;
    y += 1;
  }
  return { year: y, month: m };
}

export function wizardPrefetchRelated(
  resource: CacheResource,
  params: CacheParams,
): PrefetchSpec[] {
  if (resource !== 'calendar.month') {
    return [];
  }

  const from = Number(params.from);
  const to = Number(params.to);
  const year = Number(params.year);
  const month = Number(params.month);
  const tripType = String(params.trip_type ?? 'single');
  if (from <= 0 || to <= 0 || year <= 0 || month <= 0) {
    return [];
  }

  const related: PrefetchSpec[] = [
    {
      resource: 'calendar.month',
      params: calendarMonthParams(from, to, otherTripType(tripType), year, month),
    },
  ];

  const prev = shiftMonth(year, month, -1);
  const next = shiftMonth(year, month, 1);
  related.push({
    resource: 'calendar.month',
    params: calendarMonthParams(from, to, tripType, prev.year, prev.month),
  });
  related.push({
    resource: 'calendar.month',
    params: calendarMonthParams(from, to, tripType, next.year, next.month),
  });

  return related;
}
