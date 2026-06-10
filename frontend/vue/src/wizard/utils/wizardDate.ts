export function pad2(n: number): string {
  return n < 10 ? `0${n}` : String(n);
}

export function ymdFromParts(year: number, month: number, day: number): string {
  if (!Number.isFinite(year) || !Number.isFinite(month) || !Number.isFinite(day)) {
    return '';
  }
  return `${year}-${pad2(month)}-${pad2(day)}`;
}

export { addCalendarMonths } from '../../utils/calendarDate';

export function todayYearMonth(): { year: number; month: number } {
  const d = new Date();
  return { year: d.getFullYear(), month: d.getMonth() + 1 };
}

export function daysInMonth(year: number, month: number): number {
  return new Date(year, month, 0).getDate();
}

export function monthStartColumn(year: number, month: number, startOfWeek: number): number {
  const first = new Date(year, month - 1, 1);
  return (first.getDay() - startOfWeek + 7) % 7;
}

export { formatYmdForDisplay } from '../../utils/datetime';

export function calendarMonthTitle(year: number, month: number, monthNames?: string[]): string {
  const label = monthNames?.[month - 1] || String(month);
  return `${label} ${year}`;
}
