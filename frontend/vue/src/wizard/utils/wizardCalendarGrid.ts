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
  const rows: WizardCalCell[][] = [];
  for (let i = 0; i < flat.length; i += 7) {
    rows.push(flat.slice(i, i + 7));
  }
  return rows;
}

export function orderedWeekdayHeaders(abbrev: string[], startOfWeek: number): string[] {
  const out: string[] = [];
  for (let i = 0; i < 7; i++) {
    out.push(abbrev[(startOfWeek + i) % 7] || '');
  }
  return out;
}
