import { describe, expect, it } from 'vitest';
import { monthDayButtonAria, monthDayCountTitle } from '../src/utils/monthDayLabels';

describe('monthDayCountTitle', () => {
  it('substitutes count into template', () => {
    expect(monthDayCountTitle('%d turer denna dag', 4)).toBe('4 turer denna dag');
  });
});

describe('monthDayButtonAria', () => {
  const running = { ymd: '2026-06-06', count: 4, running: true };

  it('includes tour count when showCounts is enabled', () => {
    expect(
      monthDayButtonAria(6, running, {
        showCounts: true,
        countTitle: '%d turer (alla linjer)',
        runningLabel: 'Trafikdag',
      }),
    ).toBe('6, Trafikdag, 4 turer (alla linjer)');
  });

  it('omits count when showCounts is disabled', () => {
    expect(
      monthDayButtonAria(6, running, {
        showCounts: false,
        countTitle: '%d turer',
        runningLabel: 'Trafikdag',
      }),
    ).toBe('6, Trafikdag');
  });
});
