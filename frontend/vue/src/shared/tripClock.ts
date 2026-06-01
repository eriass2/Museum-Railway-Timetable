import { parseTripClock } from './prices';

export function formatTripClock(time: string): string {
  return time ? time.replace(':', '.') : '—';
}

export function waitMinutesBetween(arrival: string, departure: string): number | null {
  const arr = parseTripClock(arrival);
  const dep = parseTripClock(departure);
  if (arr === null || dep === null || dep < arr) {
    return null;
  }
  return dep - arr;
}
