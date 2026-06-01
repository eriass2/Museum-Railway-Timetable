import type { MonthDayMeta } from '../config/types';

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
  },
): string {
  if (!info.running) {
    return `${day}`;
  }
  const running = opts.runningLabel || 'Trafikdag';
  if (opts.showCounts && info.count && info.count > 0 && opts.countTitle) {
    return `${day}, ${running}, ${monthDayCountTitle(opts.countTitle, info.count)}`;
  }
  return `${day}, ${running}`;
}
