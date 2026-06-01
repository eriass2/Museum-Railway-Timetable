import { chunkWeekRows } from '../../utils/calendarGrid';
import type { CalendarDayInfo, CalendarDayStatus } from '../../shared/calendarDay';
import { calendarDayStatus, normalizeCalendarDay } from '../../shared/calendarDay';
import { monthStartColumn, daysInMonth, ymdFromParts } from './wizardDate';

export type WizardCalCell =
  | { kind: 'pad' }
  | { kind: 'day'; day: number; ymd: string; status: CalendarDayStatus; type?: string };

export function countBookableDaysInMonth(
  year: number,
  month: number,
  daysMap: Record<string, CalendarDayInfo | CalendarDayStatus>,
): number {
  const prefix = `${year}-${String(month).padStart(2, '0')}`;
  let count = 0;
  for (const [ymd, value] of Object.entries(daysMap)) {
    if (ymd.startsWith(prefix) && calendarDayStatus(value) === 'ok') {
      count += 1;
    }
  }
  return count;
}

export function buildWizardCalendarGrid(
  year: number,
  month: number,
  startOfWeek: number,
  daysMap: Record<string, CalendarDayInfo | CalendarDayStatus>,
): WizardCalCell[][] {
  const lastDay = daysInMonth(year, month);
  const startCol = monthStartColumn(year, month, startOfWeek);
  const flat: WizardCalCell[] = [];
  for (let i = 0; i < startCol; i++) {
    flat.push({ kind: 'pad' });
  }
  for (let d = 1; d <= lastDay; d++) {
    const ymd = ymdFromParts(year, month, d);
    const info = normalizeCalendarDay(daysMap[ymd]);
    flat.push({
      kind: 'day',
      day: d,
      ymd,
      status: info.status,
      type: info.type,
    });
  }
  while (flat.length % 7 !== 0) {
    flat.push({ kind: 'pad' });
  }
  return chunkWeekRows(flat);
}

export { orderedWeekdayHeaders } from '../../utils/calendarGrid';
