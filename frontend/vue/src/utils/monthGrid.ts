import type { MonthDayMeta } from '../config/types';

export type { MonthDayMeta };

export type MonthGridCell =
  | { kind: 'empty' }
  | { kind: 'day'; day: number; info: MonthDayMeta };

export function buildMonthGrid(
  daysInMonth: number,
  weekdayFirst: number,
  weekdayFirstSunday: number,
  startMonday: boolean,
  dates: Record<number, MonthDayMeta>,
): MonthGridCell[] {
  const lead = startMonday ? weekdayFirst - 1 : weekdayFirstSunday;

  const cells: MonthGridCell[] = [];
  for (let i = 0; i < lead; i++) {
    cells.push({ kind: 'empty' });
  }
  for (let d = 1; d <= daysInMonth; d++) {
    cells.push({
      kind: 'day',
      day: d,
      info: dates[d] || { ymd: '', count: 0, running: false },
    });
  }
  while (cells.length % 7 !== 0) {
    cells.push({ kind: 'empty' });
  }
  return cells;
}
