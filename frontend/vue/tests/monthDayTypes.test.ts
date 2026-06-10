import { describe, expect, it } from 'vitest';
import { resolveMonthDayTypes } from '../src/utils/monthDayTypes';

describe('resolveMonthDayTypes', () => {
  it('prefers types array when present', () => {
    expect(
      resolveMonthDayTypes({ types: ['green', 'orange'], type: 'yellow', running: true }),
    ).toEqual(['green', 'orange']);
  });

  it('falls back to single type', () => {
    expect(resolveMonthDayTypes({ type: 'yellow', running: true })).toEqual(['yellow']);
  });

  it('returns empty when no types', () => {
    expect(resolveMonthDayTypes({ running: false })).toEqual([]);
  });
});
