import type { JourneyConnection } from '../types';

const cache = new Map<string, JourneyConnection[]>();

export function tripConnectionsCacheKey(
  legCtx: 'outbound' | 'return',
  fromId: number,
  toId: number,
  dateYmd: string,
  outboundArrival: string,
): string {
  return `${legCtx}|${fromId}|${toId}|${dateYmd}|${outboundArrival}`;
}

export function getTripConnectionsCache(key: string): JourneyConnection[] | undefined {
  return cache.get(key);
}

export function setTripConnectionsCache(key: string, connections: JourneyConnection[]): void {
  cache.set(key, connections);
}

export function clearTripConnectionsCache(): void {
  cache.clear();
}
