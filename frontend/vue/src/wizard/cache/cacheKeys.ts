export type CacheResource = 'calendar.month' | 'journey.search';

export type CacheParams = Record<string, string | number>;

export function buildResourceCacheKey(
  generation: number,
  resource: CacheResource,
  params: CacheParams,
): string {
  const parts = [String(generation), resource];
  const names = Object.keys(params).sort();
  for (const name of names) {
    parts.push(`${name}=${String(params[name])}`);
  }
  return parts.join('|');
}

export function calendarMonthParams(
  fromId: number,
  toId: number,
  tripType: string,
  year: number,
  month: number,
): CacheParams {
  return {
    from: fromId,
    to: toId,
    trip_type: tripType === 'return' ? 'return' : 'single',
    year,
    month,
  };
}

export function journeySearchParams(
  legCtx: 'outbound' | 'return',
  fromId: number,
  toId: number,
  dateYmd: string,
  outboundArrival: string,
): CacheParams {
  return {
    leg: legCtx,
    from: fromId,
    to: toId,
    date: dateYmd,
    trip_type: legCtx === 'return' ? 'return' : 'single',
    outbound_arrival: outboundArrival,
  };
}
