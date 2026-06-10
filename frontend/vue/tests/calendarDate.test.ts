import { describe, expect, it } from 'vitest';
import { addCalendarMonths } from '../src/utils/calendarDate';

describe('calendarDate', () => {
  it('addCalendarMonths rolls year', () => {
    expect(addCalendarMonths(2026, 12, 1)).toEqual({ year: 2027, month: 1 });
    expect(addCalendarMonths(2026, 1, -1)).toEqual({ year: 2025, month: 12 });
  });
});
