/** Keep ?mrt_month=YYYY-MM in sync without reloading the page. */
export function syncMonthCalendarQuery(year: number, month: number): void {
  if (typeof window === 'undefined') {
    return;
  }
  const url = new URL(window.location.href);
  url.searchParams.set('mrt_month', `${year}-${String(month).padStart(2, '0')}`);
  window.history.replaceState(null, '', url.toString());
}

/** Keep ?mrt_date=YYYY-MM-DD in sync when a day timetable is open. */
export function syncDayCalendarQuery(ymd: string | null): void {
  if (typeof window === 'undefined') {
    return;
  }
  const url = new URL(window.location.href);
  if (ymd) {
    url.searchParams.set('mrt_date', ymd);
    url.searchParams.set('mrt_month', ymd.slice(0, 7));
  } else {
    url.searchParams.delete('mrt_date');
  }
  window.history.replaceState(null, '', url.toString());
}
