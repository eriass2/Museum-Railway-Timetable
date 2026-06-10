import { describe, expect, it } from 'vitest';
import { formatPriceCell, priceKeysFromMap } from '../src/shared/prices';

describe('formatPriceCell', () => {
  it('formats numeric prices with kr', () => {
    expect(formatPriceCell(120, {})).toBe('120 kr');
  });

  it('returns dash for empty', () => {
    expect(formatPriceCell(null, { priceDash: '—' })).toBe('—');
  });
});

describe('priceKeysFromMap', () => {
  it('returns object keys in order', () => {
    expect(priceKeysFromMap({ adult: 'Vuxen', child: 'Barn' })).toEqual(['adult', 'child']);
  });
});
