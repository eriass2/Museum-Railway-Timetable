import type { PriceMatrix } from './priceTypes';

export type { PriceCfg, PriceMatrix, PriceMatrixCell, PriceMatrixByZone, PriceStationZones } from './priceTypes';

/** Keys from a label map (schema order from server). */
export function priceKeysFromMap(map: Record<string, string>): string[] {
  return Object.keys(map);
}

/** Built-in defaults — runtime uses labels from server config. */
export const DEFAULT_PRICE_TYPE_KEYS = ['single', 'return', 'day'] as const;
export const DEFAULT_PRICE_CAT_KEYS = ['adult', 'child_4_15', 'child_0_3', 'student_senior'] as const;

/** @deprecated Use priceKeysFromMap(labels.tickets) or matrix keys from API. */
export const PRICE_TYPE_KEYS = DEFAULT_PRICE_TYPE_KEYS;
/** @deprecated Use priceKeysFromMap(labels.categories). */
export const PRICE_CAT_KEYS = DEFAULT_PRICE_CAT_KEYS;

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
