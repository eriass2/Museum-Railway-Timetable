import type { TripType } from '../types';
import type { WizardCfg } from './wizardLabels';
import { cfgStr } from './wizardLabels';

export const PRICE_TYPE_KEYS = ['single', 'return', 'day'] as const;
export const PRICE_CAT_KEYS = ['adult', 'child_4_15', 'child_0_3', 'student_senior'] as const;

type PriceMatrix = Record<string, Record<string, string | number | null>>;

export function zonesForStationPair(fromId: number, toId: number, cfg: WizardCfg): number {
  const raw = cfg.priceStationZones;
  const map = typeof raw === 'object' && raw ? (raw as Record<string, number[]>) : {};
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

function matrixForZone(cfg: WizardStrings, zones: number): PriceMatrix {
  const byZone = cfg.priceMatrixByZone as Record<string, Record<string, Record<string, unknown>>> | undefined;
  const zoneKey = String(Math.max(1, Math.min(4, zones || 4)));
  if (!byZone) {
    return (cfg.priceMatrix as PriceMatrix) || {};
  }
  const out: PriceMatrix = {};
  PRICE_TYPE_KEYS.forEach((tk) => {
    out[tk] = {};
    PRICE_CAT_KEYS.forEach((ck) => {
      out[tk][ck] = (byZone[tk]?.[ck]?.[zoneKey] as string | number | null) ?? null;
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

export function formatPriceCell(v: unknown, cfg: WizardCfg): string {
  if (v === null || v === undefined || v === '') {
    return cfgStr(cfg, 'priceDash', '—');
  }
  const s = String(v).trim();
  return /^\d+$/.test(s) ? `${s} kr` : s;
}

export function priceMatrixForTrip(
  tripType: TripType,
  cfg: WizardCfg,
  zones: number,
): { matrix: PriceMatrix; activeType: string } | null {
  const matrix = matrixForZone(cfg, zones);
  if (!matrixHasAnyPrice(matrix)) {
    return null;
  }
  return { matrix, activeType: tripType === 'return' ? 'return' : 'single' };
}
