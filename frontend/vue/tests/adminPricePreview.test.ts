import { describe, expect, it } from 'vitest';
import type { PricesPayload } from '../src/admin/api/adminRest';
import {
  adminAfternoonVisitorNote,
  buildAdminPricePreviewTrip,
  effectivePricingZones,
  hasMatrixZonesBeyondCap,
  priceMatrixHasAnyValue,
  priceMatrixRowForZone,
  resolvePricingZone,
} from '../src/admin/utils/prices/adminPricePreview';

describe('adminPricePreview', () => {
  const payload: PricesPayload = {
    matrix: {
      single: {
        adult: { 1: 80, 2: 110 },
        child_4_15: { 1: 30, 2: 30 },
      },
      return: {
        adult: { 1: 160, 2: 220 },
      },
      day: {
        adult: { 1: 280, 2: 280 },
      },
    },
    ticket_types: {
      single: 'Enkel',
      return: 'Retur',
      day: 'Dagskort',
    },
    categories: {
      adult: 'Vuxen',
      child_4_15: 'Barn',
    },
    zones: [1, 2],
    zone_cap: 2,
    afternoon_return: {
      adult: 160,
      child_4_15: 60,
    },
  };

  it('detects configured matrix cells', () => {
    expect(priceMatrixHasAnyValue(payload)).toBe(true);
    expect(
      priceMatrixHasAnyValue({
        ...payload,
        matrix: { single: { adult: { 1: null } } },
      }),
    ).toBe(false);
  });

  it('selects zone column capped by zone_cap', () => {
    expect(priceMatrixRowForZone(payload, 'single', 9)).toEqual({
      adult: 110,
      child_4_15: 30,
    });
    expect(resolvePricingZone(payload, 9)).toBe(2);
  });

  it('lists effective pricing zones up to zone_cap', () => {
    expect(effectivePricingZones(payload)).toEqual([1, 2]);
    expect(
      hasMatrixZonesBeyondCap({
        ...payload,
        zones: [1, 2, 3, 4],
        zone_cap: 3,
      }),
    ).toBe(true);
    expect(
      effectivePricingZones({
        ...payload,
        zones: [1, 2, 3, 4],
        zone_cap: 3,
      }),
    ).toEqual([1, 2, 3]);
  });

  it('builds afternoon return preview', () => {
    const trip = buildAdminPricePreviewTrip(payload, 'return', 2, true);
    expect(trip.isAfternoonReturn).toBe(true);
    expect(trip.matrix.return?.adult).toBe(160);
  });

  it('formats visitor note with threshold time', () => {
    const note = adminAfternoonVisitorNote(
      { strings: { pricesAfternoonPublicNote: 'Gäller från kl %1$s.' } } as never,
      900,
    );
    expect(note).toBe('Gäller från kl 15:00.');
    expect(adminAfternoonVisitorNote({ strings: {} } as never, 0)).toBe('');
  });
});
