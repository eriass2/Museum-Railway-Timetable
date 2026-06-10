import { describe, expect, it } from 'vitest';
import {
  formatHhmmForDisplay,
  formatYmdForDisplay,
  hhmmToMinutes,
  minutesToHhmm,
  padHhmm,
  todayYmd,
  validateHhmm,
  validateYmd,
} from '../src/utils/datetime';

describe('datetime', () => {
  it('validates YMD and HH:MM', () => {
    expect(validateYmd('2026-06-09')).toBe(true);
    expect(validateYmd('2026-6-09')).toBe(false);
    expect(validateHhmm('15:00')).toBe(true);
    expect(validateHhmm('')).toBe(true);
    expect(validateHhmm('25:00')).toBe(false);
  });

  it('converts between minutes and HH:MM', () => {
    expect(hhmmToMinutes('15:00')).toBe(900);
    expect(minutesToHhmm(900)).toBe('15:00');
    expect(hhmmToMinutes('bad')).toBeNull();
  });

  it('pads loose HH:MM input', () => {
    expect(padHhmm('9:05')).toBe('09:05');
    expect(padHhmm('')).toBe('');
  });

  it('formats display strings', () => {
    expect(formatHhmmForDisplay('10:30')).toBe('10.30');
    expect(formatHhmmForDisplay('')).toBe('—');
    expect(formatYmdForDisplay('2026-03-15', ['januari', 'februari', 'mars'])).toBe('15 mars 2026');
  });

  it('todayYmd returns ISO date', () => {
    expect(todayYmd()).toMatch(/^\d{4}-\d{2}-\d{2}$/);
  });
});
