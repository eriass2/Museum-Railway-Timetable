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

export function formatYmdForDisplay(
  ymd: string,
  monthNames?: string[],
): string {
  const p = ymd.split('-');
  if (p.length !== 3) {
    return ymd;
  }
  const mo = parseInt(p[1], 10);
  const day = parseInt(p[2], 10);
  if (monthNames && monthNames[mo - 1]) {
    return `${day} ${monthNames[mo - 1]} ${p[0]}`;
  }
  return ymd;
}

export function calendarMonthTitle(year: number, month: number, monthNames?: string[]): string {
  const label = monthNames?.[month - 1] || String(month);
  return `${label} ${year}`;
}
