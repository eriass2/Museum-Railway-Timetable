import { formatHhmmForDisplay, hhmmToMinutes } from '../utils/datetime';

export function parseTripClock(hhmm: string): number | null {
  return hhmmToMinutes(hhmm);
}

export function formatTripClock(time: string): string {
  return formatHhmmForDisplay(time);
}

export function waitMinutesBetween(arrival: string, departure: string): number | null {
  const arr = parseTripClock(arrival);
  const dep = parseTripClock(departure);
  if (arr === null || dep === null || dep < arr) {
    return null;
  }
  return dep - arr;
}
