import { describe, expect, it } from 'vitest';
import { formatTripClock } from '../src/shared/tripClock';
import { formatDuration, isWarningNotice } from '../src/wizard/utils/format';

describe('formatTripClock', () => {
  it('replaces colon with dot', () => {
    expect(formatTripClock('10:30')).toBe('10.30');
  });
});

describe('formatDuration', () => {
  it('formats sub-hour durations', () => {
    expect(formatDuration(45, {})).toBe('45 min');
  });

  it('formats hours and minutes', () => {
    expect(formatDuration(90, {})).toBe('1 tim 30 min');
  });
});

describe('isWarningNotice', () => {
  it('detects Swedish warning keywords', () => {
    expect(isWarningNotice('Ersattningsbuss pga brand')).toBe(true);
    expect(isWarningNotice('Normal trafik')).toBe(false);
  });
});
