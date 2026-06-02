export function parseTripClock(hhmm: string): number | null {
  const match = /^(\d{1,2}):(\d{2})$/.exec(hhmm.trim());
  if (!match) {
    return null;
  }
  return Number.parseInt(match[1], 10) * 60 + Number.parseInt(match[2], 10);
}

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
