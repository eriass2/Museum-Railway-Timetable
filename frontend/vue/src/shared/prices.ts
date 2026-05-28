import type { PriceCfg, PriceMatrix } from './priceTypes';

export type { PriceCfg, PriceMatrix, PriceMatrixCell, PriceMatrixByZone, PriceStationZones } from './priceTypes';

export const PRICE_TYPE_KEYS = ['single', 'return', 'day'] as const;
export const PRICE_CAT_KEYS = ['adult', 'child_4_15', 'child_0_3', 'student_senior'] as const;

export type PriceTripType = 'single' | 'return' | 'day';

export function zonesForStationPair(fromId: number, toId: number, cfg: PriceCfg): number {
  const map = cfg.priceStationZones ?? {};
  const fromZones = map[String(fromId)] || [];
  const toZones = map[String(toId)] || [];
  let best = 4;
  if (!fromZones.length || !toZones.length) {
    return best;
  }
  fromZones.forEach((fz) => {
    toZones.forEach((tz) => {
      const span = Math.abs(tz - fz) + 1;
      if (!Number.isNaN(span)) {
        best = Math.min(best, span);
      }
    });
  });
  return Math.max(1, Math.min(4, best));
}

function matrixForZone(cfg: PriceCfg, zones: number): PriceMatrix {
  const byZone = cfg.priceMatrixByZone;
  const zoneKey = String(Math.max(1, Math.min(4, zones || 4)));
  if (!byZone) {
    return cfg.priceMatrix ?? {};
  }
  const out: PriceMatrix = {};
  PRICE_TYPE_KEYS.forEach((tk) => {
    out[tk] = {};
    PRICE_CAT_KEYS.forEach((ck) => {
      out[tk][ck] = byZone[tk]?.[ck]?.[zoneKey] ?? null;
    });
  });
  return out;
}

export function matrixHasAnyPrice(matrix: PriceMatrix): boolean {
  return PRICE_TYPE_KEYS.some((tk) =>
    PRICE_CAT_KEYS.some((ck) => {
      const v = matrix[tk]?.[ck];
      return v !== null && v !== undefined && v !== '';
    }),
  );
}

export function formatPriceCell(v: unknown, cfg: PriceCfg): string {
  if (v === null || v === undefined || v === '') {
    return cfg.priceDash?.trim() || '—';
  }
  const s = String(v).trim();
  return /^\d+$/.test(s) ? `${s} kr` : s;
}

export function priceMatrixForTrip(
  tripType: PriceTripType,
  cfg: PriceCfg,
  zones: number,
): { matrix: PriceMatrix; activeType: string } | null {
  const matrix = matrixForZone(cfg, zones);
  if (!matrixHasAnyPrice(matrix)) {
    return null;
  }
  return { matrix, activeType: tripType === 'return' ? 'return' : 'single' };
}
