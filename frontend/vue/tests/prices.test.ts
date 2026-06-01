import { describe, expect, it } from 'vitest';
import {
  dayTicketMatrix,
  formatPriceCell,
  matrixHasAnyPrice,
  parseTripClock,
  priceMatrixForTrip,
  pricingZoneCount,
  qualifiesForAfternoonReturn,
  zonesForStationPair,
} from '../src/shared/prices';

describe('zonesForStationPair', () => {
  it('returns 3 when zones unknown', () => {
    expect(zonesForStationPair(1, 2, {})).toBe(3);
  });

  it('computes span from zone maps', () => {
    const cfg = {
      priceStationZones: { '1': [1], '2': [3] },
    };
    expect(zonesForStationPair(1, 2, cfg)).toBe(3);
  });

  it('caps geographic span at three price zones', () => {
    const cfg = {
      priceStationZones: { '1': [1], '2': [4] },
    };
    expect(zonesForStationPair(1, 2, cfg)).toBe(3);
  });
});

describe('pricingZoneCount', () => {
  it('caps at three', () => {
    expect(pricingZoneCount(4)).toBe(3);
    expect(pricingZoneCount(2)).toBe(2);
  });
});

describe('parseTripClock', () => {
  it('parses HH:MM', () => {
    expect(parseTripClock('15:00')).toBe(900);
    expect(parseTripClock('14:59')).toBe(899);
  });
});

describe('qualifiesForAfternoonReturn', () => {
  it('requires return trip with both legs after 15:00', () => {
    expect(qualifiesForAfternoonReturn('return', '15:00', '16:00')).toBe(true);
    expect(qualifiesForAfternoonReturn('return', '14:59', '16:00')).toBe(false);
    expect(qualifiesForAfternoonReturn('single', '15:00', '16:00')).toBe(false);
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
  const cfg = {
    priceMatrixByZone: {
      single: {
        adult: { '2': 110 },
        child_4_15: { '2': 30 },
        child_0_3: { '2': 0 },
        student_senior: { '2': 100 },
      },
      return: {
        adult: { '2': 220 },
        child_4_15: { '2': 60 },
        child_0_3: { '2': 0 },
        student_senior: { '2': 200 },
      },
      day: {
        adult: { '2': 280 },
        child_4_15: { '2': 80 },
        child_0_3: { '2': 0 },
        student_senior: { '2': 260 },
      },
    },
    afternoonReturnPrices: {
      adult: 160,
      child_4_15: 60,
      child_0_3: 0,
      student_senior: 140,
    },
  };

  it('returns null when matrix has no prices', () => {
    expect(priceMatrixForTrip('single', {}, 4)).toBeNull();
  });

  it('selects return type for return trips', () => {
    const result = priceMatrixForTrip('return', cfg, 2);
    expect(result?.activeType).toBe('return');
    expect(result?.matrix.return?.adult).toBe(220);
    expect(result?.matrix.return?.child_4_15).toBe(60);
  });

  it('uses flat afternoon return prices when both legs depart after 15:00', () => {
    const result = priceMatrixForTrip('return', cfg, 2, {
      outboundDeparture: '15:10',
      inboundDeparture: '16:00',
    });
    expect(result?.isAfternoonReturn).toBe(true);
    expect(result?.matrix.return?.adult).toBe(160);
    expect(result?.matrix.return?.student_senior).toBe(140);
  });
});

describe('dayTicketMatrix', () => {
  it('returns day row from zone matrix', () => {
    const cfg = {
      priceMatrixByZone: {
        day: { adult: { '2': 280 }, child_4_15: { '2': 80 } },
      },
    };
    const day = dayTicketMatrix(cfg, 2);
    expect(day?.day?.adult).toBe(280);
  });
});
