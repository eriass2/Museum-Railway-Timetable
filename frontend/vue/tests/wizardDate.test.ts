import { describe, expect, it } from 'vitest';
import {
  addCalendarMonths,
  daysInMonth,
  formatYmdForDisplay,
  monthStartColumn,
  pad2,
  ymdFromParts,
} from '../src/wizard/utils/wizardDate';

describe('wizardDate', () => {
  it('pad2 zero-pads single digits', () => {
    expect(pad2(3)).toBe('03');
    expect(pad2(12)).toBe('12');
  });

  it('ymdFromParts formats ISO date', () => {
    expect(ymdFromParts(2026, 5, 9)).toBe('2026-05-09');
  });

  it('addCalendarMonths rolls year', () => {
    expect(addCalendarMonths(2026, 12, 1)).toEqual({ year: 2027, month: 1 });
  });

  it('daysInMonth handles leap year', () => {
    expect(daysInMonth(2024, 2)).toBe(29);
    expect(daysInMonth(2025, 2)).toBe(28);
  });

  it('formatYmdForDisplay uses month names', () => {
    const names = ['Jan', 'Feb', 'Mar'];
    expect(formatYmdForDisplay('2026-03-15', names)).toBe('Mar 15, 2026');
  });

  it('monthStartColumn respects startOfWeek', () => {
    expect(monthStartColumn(2026, 5, 1)).toBeGreaterThanOrEqual(0);
    expect(monthStartColumn(2026, 5, 1)).toBeLessThan(7);
  });
});
