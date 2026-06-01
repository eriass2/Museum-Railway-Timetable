import { describe, expect, it } from 'vitest';
import {
  calendarDayStatus,
  normalizeCalendarDay,
  timetableTypeClass,
  timetableTypeDotClass,
} from '../src/shared/calendarDay';

describe('calendarDay', () => {
  it('normalizes legacy string status', () => {
    expect(normalizeCalendarDay('ok')).toEqual({ status: 'ok' });
  });

  it('reads status and type from objects', () => {
    expect(normalizeCalendarDay({ status: 'ok', type: 'green' })).toEqual({
      status: 'ok',
      type: 'green',
    });
    expect(calendarDayStatus({ status: 'traffic_no_match', type: 'red' })).toBe('traffic_no_match');
  });

  it('maps timetable type slugs to CSS classes', () => {
    expect(timetableTypeClass('green')).toBe('mrt-day--green');
    expect(timetableTypeClass('yellow', 'mrt-calendar-day')).toBe('mrt-calendar-day--yellow');
    expect(timetableTypeDotClass('orange')).toBe('mrt-dot--orange');
    expect(timetableTypeClass('')).toBeUndefined();
  });
});
