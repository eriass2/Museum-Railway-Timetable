import { describe, expect, it } from 'vitest';
import { monthLegendHints } from '../src/utils/monthLegendHints';

describe('monthLegendHints', () => {
  it('returns no hints (traffic colours shown on day cells)', () => {
    expect(monthLegendHints()).toEqual([]);
  });
});
