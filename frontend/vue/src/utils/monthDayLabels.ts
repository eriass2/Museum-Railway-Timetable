import type { MonthDayMeta } from '../config/types';
import { timetableTypeLabel } from '../shared/calendarDay';
import { resolveMonthDayTypes } from './monthDayTypes';

export function normalizeMonthDayCount(count: MonthDayMeta['count']): number {
  if (count === undefined || count === null || count === '') {
    return 0;
  }
  const n = typeof count === 'number' ? count : Number(count);
  return Number.isFinite(n) ? n : 0;
}

export function monthDayCountTitle(template: string, count: number): string {
  return template.replace('%d', String(count));
}

export function monthDayButtonAria(
  day: number,
  info: MonthDayMeta,
  opts: {
    showCounts: boolean;
    countTitle?: string;
    runningLabel?: string;
    typeLabels?: Record<string, string>;
  },
): string {
  if (!info.running) {
    return `${day}`;
  }
  const types = resolveMonthDayTypes(info);
  const typeLabel = types.length
    ? types
        .map((t) => (opts.typeLabels ? timetableTypeLabel(t, opts.typeLabels) : t))
        .join(', ')
    : opts.runningLabel || 'Trafikdag';
  if (opts.showCounts && normalizeMonthDayCount(info.count) > 0 && opts.countTitle) {
    return `${day}, ${typeLabel}, ${monthDayCountTitle(opts.countTitle, normalizeMonthDayCount(info.count))}`;
  }
  return `${day}, ${typeLabel}`;
}
