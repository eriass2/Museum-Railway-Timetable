import type { PriceCfg, PriceMatrix } from './priceTypes';

export type { PriceCfg, PriceMatrix, PriceMatrixCell, PriceMatrixByZone, PriceStationZones } from './priceTypes';

export const PRICE_TYPE_KEYS = ['single', 'return', 'day'] as const;
export const PRICE_CAT_KEYS = ['adult', 'child_4_15', 'child_0_3', 'student_senior'] as const;

export type PriceTripType = 'single' | 'return' | 'day';

export const MAX_PRICE_ZONES = 3;

export function pricingZoneCount(zones: number): number {
  if (!zones || Number.isNaN(zones)) {
    return MAX_PRICE_ZONES;
  }
  return Math.max(1, Math.min(MAX_PRICE_ZONES, zones));
}

export function parseTripClock(hhmm: string): number | null {
  const match = /^(\d{1,2}):(\d{2})$/.exec(hhmm.trim());
  if (!match) {
    return null;
  }
  return Number.parseInt(match[1], 10) * 60 + Number.parseInt(match[2], 10);
}

export function qualifiesForAfternoonReturn(
  tripType: PriceTripType,
  outboundDeparture: string,
  inboundDeparture: string,
  thresholdMinutes = 15 * 60,
): boolean {
  if (tripType !== 'return') {
    return false;
  }
  const out = parseTripClock(outboundDeparture);
  const inbound = parseTripClock(inboundDeparture);
  if (out === null || inbound === null) {
    return false;
  }
  return out >= thresholdMinutes && inbound >= thresholdMinutes;
}

export function zonesForStationPair(fromId: number, toId: number, cfg: PriceCfg): number {
  const map = cfg.priceStationZones ?? {};
  const fromZones = map[String(fromId)] || [];
  const toZones = map[String(toId)] || [];
  let best = MAX_PRICE_ZONES;
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
  return pricingZoneCount(best);
}

function matrixForZone(cfg: PriceCfg, zones: number): PriceMatrix {
  const byZone = cfg.priceMatrixByZone;
  const zoneKey = String(pricingZoneCount(zones));
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

function afternoonReturnMatrix(cfg: PriceCfg): PriceMatrix {
  const flat = cfg.afternoonReturnPrices ?? {
    adult: 160,
    child_4_15: 60,
    child_0_3: 0,
    student_senior: 140,
  };
  return {
    return: { ...flat },
  };
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

export type PriceTripOptions = {
  outboundDeparture?: string;
  inboundDeparture?: string;
};

export function priceMatrixForTrip(
  tripType: PriceTripType,
  cfg: PriceCfg,
  zones: number,
  options: PriceTripOptions = {},
): { matrix: PriceMatrix; activeType: string; isAfternoonReturn: boolean } | null {
  const isAfternoonReturn = qualifiesForAfternoonReturn(
    tripType,
    options.outboundDeparture ?? '',
    options.inboundDeparture ?? '',
  );
  const matrix = isAfternoonReturn ? afternoonReturnMatrix(cfg) : matrixForZone(cfg, zones);
  if (!matrixHasAnyPrice(matrix)) {
    return null;
  }
  return {
    matrix,
    activeType: tripType === 'return' ? 'return' : 'single',
    isAfternoonReturn,
  };
}

export function dayTicketMatrix(cfg: PriceCfg, zones: number): PriceMatrix | null {
  const matrix = matrixForZone(cfg, zones);
  if (!matrixHasAnyPrice({ day: matrix.day ?? {} })) {
    return null;
  }
  return { day: matrix.day ?? {} };
}
