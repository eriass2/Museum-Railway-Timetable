import type { PriceMatrix } from './priceTypes';

export type { PriceCfg, PriceMatrix, PriceMatrixCell, PriceMatrixByZone, PriceStationZones } from './priceTypes';

export const PRICE_TYPE_KEYS = ['single', 'return', 'day'] as const;
export const PRICE_CAT_KEYS = ['adult', 'child_4_15', 'child_0_3', 'student_senior'] as const;

export type PriceTripType = 'single' | 'return' | 'day';

/** Server-computed trip price payload (`GET /prices/trip`). */
export type TripPriceData = {
  matrix: PriceMatrix;
  activeType: string;
  isAfternoonReturn: boolean;
};

export type DayTicketData = {
  day: Record<string, string | number | null>;
};

export function formatPriceCell(v: unknown, cfg: { priceDash?: string }): string {
  if (v === null || v === undefined || v === '') {
    return cfg.priceDash?.trim() || '—';
  }
  const s = String(v).trim();
  return /^\d+$/.test(s) ? `${s} kr` : s;
}
