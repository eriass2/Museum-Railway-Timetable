export type CalendarDayStatus = 'ok' | 'traffic_no_match' | 'none';

export type CalendarDayInfo = {
  status: CalendarDayStatus;
  type?: string;
};

export type TimetableTypeSlug = 'green' | 'yellow' | 'red' | 'orange' | 'blue';

const TIMETABLE_TYPE_SLUGS = new Set<TimetableTypeSlug>([
  'green',
  'yellow',
  'red',
  'orange',
  'blue',
]);

export function normalizeCalendarDay(
  value: CalendarDayInfo | CalendarDayStatus | undefined,
): CalendarDayInfo {
  if (!value) {
    return { status: 'none' };
  }
  if (typeof value === 'string') {
    return { status: value };
  }
  return {
    status: value.status || 'none',
    type: value.type,
  };
}

export function calendarDayStatus(
  value: CalendarDayInfo | CalendarDayStatus | undefined,
): CalendarDayStatus {
  return normalizeCalendarDay(value).status;
}

export function timetableTypeClass(type?: string, prefix = 'mrt-day'): string | undefined {
  const slug = (type || '').toLowerCase();
  if (TIMETABLE_TYPE_SLUGS.has(slug as TimetableTypeSlug)) {
    return `${prefix}--${slug}`;
  }
  return undefined;
}

export function timetableTypeDotClass(type?: string): string | undefined {
  const slug = (type || '').toLowerCase();
  if (TIMETABLE_TYPE_SLUGS.has(slug as TimetableTypeSlug)) {
    return `mrt-dot--${slug}`;
  }
  return undefined;
}

export function timetableTypeBarClass(type?: string): string | undefined {
  return timetableTypeClass(type, 'mrt-day-bar');
}

export function timetableTypeMonthBarClass(type?: string): string | undefined {
  return timetableTypeClass(type, 'mrt-month-day__bar');
}

export function timetableTypeMonthDayClass(type?: string): string | undefined {
  return timetableTypeClass(type, 'mrt-month-day');
}

export function timetableTypeOverviewClass(type?: string): string | undefined {
  return timetableTypeClass(type, 'mrt-ov');
}

export function timetableTypeLabel(
  type: string,
  labels?: Record<string, string>,
): string {
  const slug = type.toLowerCase();
  if (labels?.[slug]) {
    return labels[slug];
  }
  return type.toUpperCase();
}
