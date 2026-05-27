import { describe, expect, it } from 'vitest';
import {
  zonesForStationPair,
  formatPriceCell,
  matrixHasAnyPrice,
  priceMatrixForTrip,
} from '../src/wizard/utils/prices';

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

describe('matrixHasAnyPrice', () => {
  it('is false for empty matrix', () => {
    expect(matrixHasAnyPrice({})).toBe(false);
  });

  it('is true when a cell has value', () => {
    expect(matrixHasAnyPrice({ single: { adult: 120 } })).toBe(true);
  });
});

describe('priceMatrixForTrip', () => {
  it('returns null when matrix has no prices', () => {
    expect(priceMatrixForTrip('single', {}, 4)).toBeNull();
  });

  it('selects return type for return trips', () => {
    const cfg = {
      priceMatrixByZone: {
        single: { adult: { '2': 100 } },
        return: { adult: { '2': 180 } },
      },
    };
    const result = priceMatrixForTrip('return', cfg, 2);
    expect(result?.activeType).toBe('return');
  });
});
