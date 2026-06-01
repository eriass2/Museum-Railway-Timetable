import { describe, expect, it } from 'vitest';
import { monthLegendHints } from '../src/utils/monthLegendHints';

describe('monthLegendHints', () => {
  const countHint = 'Siffran visar antal turer';
  const clickHint = 'Klicka för att visa tidtabell';

  it('omits count hint when show_counts is off', () => {
    expect(monthLegendHints(false, countHint, clickHint)).toEqual([`(${clickHint})`]);
  });

  it('includes count hint when show_counts is on', () => {
    expect(monthLegendHints(true, countHint, clickHint)).toEqual([
      `(${countHint})`,
      `(${clickHint})`,
    ]);
  });
});
