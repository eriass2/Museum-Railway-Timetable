import { describe, expect, it } from 'vitest';
import { formatPriceCell } from '../src/shared/prices';

describe('formatPriceCell', () => {
  it('formats numeric prices with kr', () => {
    expect(formatPriceCell(120, {})).toBe('120 kr');
  });

  it('returns dash for empty', () => {
    expect(formatPriceCell(null, { priceDash: '—' })).toBe('—');
  });
});
