import { describe, expect, it } from 'vitest';
import { minutesToTimeInput, timeInputToMinutes } from '../src/admin/utils/settingsTime';

describe('settingsTime', () => {
  it('round-trips common afternoon threshold', () => {
    expect(minutesToTimeInput(900)).toBe('15:00');
    expect(timeInputToMinutes('15:00')).toBe(900);
  });

  it('clamps invalid minutes', () => {
    expect(minutesToTimeInput(2000)).toBe('23:59');
  });
});
