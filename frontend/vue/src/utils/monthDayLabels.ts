import type { MonthDayMeta } from '../config/types';
import { timetableTypeLabel } from '../shared/calendarDay';

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
  const typeLabel =
    info.type && opts.typeLabels
      ? timetableTypeLabel(info.type, opts.typeLabels)
      : opts.runningLabel || 'Trafikdag';
  if (opts.showCounts && info.count && info.count > 0 && opts.countTitle) {
    return `${day}, ${typeLabel}, ${monthDayCountTitle(opts.countTitle, info.count)}`;
  }
  return `${day}, ${typeLabel}`;
}
