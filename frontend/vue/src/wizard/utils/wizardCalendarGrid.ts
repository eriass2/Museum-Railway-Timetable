import { chunkWeekRows } from '../../utils/calendarGrid';
import type { CalendarDayStatus } from '../types';
import { monthStartColumn, daysInMonth, ymdFromParts } from './wizardDate';

export type WizardCalCell =
  | { kind: 'pad' }
  | { kind: 'day'; day: number; ymd: string; status: CalendarDayStatus };

export function buildWizardCalendarGrid(
  year: number,
  month: number,
  startOfWeek: number,
  daysMap: Record<string, CalendarDayStatus>,
): WizardCalCell[][] {
  const lastDay = daysInMonth(year, month);
  const startCol = monthStartColumn(year, month, startOfWeek);
  const flat: WizardCalCell[] = [];
  for (let i = 0; i < startCol; i++) {
    flat.push({ kind: 'pad' });
  }
  for (let d = 1; d <= lastDay; d++) {
    const ymd = ymdFromParts(year, month, d);
    flat.push({ kind: 'day', day: d, ymd, status: daysMap[ymd] || 'none' });
  }
  while (flat.length % 7 !== 0) {
    flat.push({ kind: 'pad' });
  }
  return chunkWeekRows(flat);
}

export { orderedWeekdayHeaders } from '../../utils/calendarGrid';
