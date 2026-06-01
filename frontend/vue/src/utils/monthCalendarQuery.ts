/** Keep ?mrt_month=YYYY-MM in sync without reloading the page. */
export function syncMonthCalendarQuery(year: number, month: number): void {
  if (typeof window === 'undefined') {
    return;
  }
  const url = new URL(window.location.href);
  url.searchParams.set('mrt_month', `${year}-${String(month).padStart(2, '0')}`);
  window.history.replaceState(null, '', url.toString());
}
