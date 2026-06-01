import type { MonthDayMeta } from '../config/types';

/** Timetable type slugs for a month day (supports legacy single `type`). */
export function resolveMonthDayTypes(info: MonthDayMeta): string[] {
  if (info.types?.length) {
    return info.types.filter((t) => typeof t === 'string' && t.trim() !== '');
  }
  if (info.type?.trim()) {
    return [info.type.trim()];
  }
  return [];
}
