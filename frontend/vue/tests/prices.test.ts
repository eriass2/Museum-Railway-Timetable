import { describe, expect, it } from 'vitest';
import { zonesForStationPair, formatPriceCell } from '../src/wizard/utils/prices';

describe('zonesForStationPair', () => {
  it('returns 4 when zones unknown', () => {
    expect(zonesForStationPair(1, 2, {})).toBe(4);
  });

  it('computes span from zone maps', () => {
    const cfg = {
      priceStationZones: { '1': [1], '2': [3] },
    };
    expect(zonesForStationPair(1, 2, cfg)).toBe(3);
  });
});

describe('formatPriceCell', () => {
  it('formats numeric prices with kr', () => {
    expect(formatPriceCell(120, {})).toBe('120 kr');
  });

  it('returns dash for empty', () => {
    expect(formatPriceCell(null, { priceDash: '—' })).toBe('—');
  });
});
