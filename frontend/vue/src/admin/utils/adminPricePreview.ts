import type { PricesPayload } from '../api/adminRest';
import type { DayTicketData, TripPriceData } from '../../shared/prices';
import type { PriceTableLabels } from '../../shared/priceLabels';
import type { PriceCfg } from '../../shared/priceTypes';
import { adminStr } from './adminLabels';
import type { AdminClientConfig } from '../types';

export function priceMatrixRowForZone(
  payload: PricesPayload,
  ticketType: string,
  zone: number,
): Record<string, number | null> {
  const zoneKey = Math.max(1, Math.min(payload.zone_cap, zone));
  const row: Record<string, number | null> = {};
  for (const cat of Object.keys(payload.categories)) {
    row[cat] = payload.matrix[ticketType]?.[cat]?.[zoneKey] ?? null;
  }
  return row;
}

export function priceMatrixHasAnyValue(payload: PricesPayload): boolean {
  for (const ticket of Object.keys(payload.matrix)) {
    for (const cat of Object.keys(payload.matrix[ticket] ?? {})) {
      for (const zone of payload.zones) {
        const value = payload.matrix[ticket]?.[cat]?.[zone];
        if (value !== null && value !== undefined) {
          return true;
        }
      }
    }
  }
  return false;
}

export function buildAdminPricePreviewTrip(
  payload: PricesPayload,
  ticketType: string,
  zone: number,
  afternoonMode: boolean,
): TripPriceData {
  if (afternoonMode && ticketType === 'return') {
    const row: Record<string, number | null> = {};
    for (const cat of Object.keys(payload.categories)) {
      row[cat] = payload.afternoon_return[cat] ?? null;
    }
    return {
      matrix: { return: row },
      activeType: 'return',
      isAfternoonReturn: true,
    };
  }
  return {
    matrix: { [ticketType]: priceMatrixRowForZone(payload, ticketType, zone) },
    activeType: ticketType,
    isAfternoonReturn: false,
  };
}

export function buildAdminPricePreviewDay(
  payload: PricesPayload,
  zone: number,
): DayTicketData | null {
  if (!payload.ticket_types.day) {
    return null;
  }
  const row = priceMatrixRowForZone(payload, 'day', zone);
  const hasAny = Object.values(row).some((v) => v !== null && v !== undefined);
  if (!hasAny) {
    return null;
  }
  return { day: row };
}

export function adminPriceTableLabels(
  cfg: AdminClientConfig,
  payload: PricesPayload,
  zone: number,
  showZoneCount: boolean,
): PriceTableLabels {
  let titleSuffix = '';
  if (showZoneCount) {
    titleSuffix = `(${adminStr(cfg, 'pricesZoneLabel', 'Zon')} ${zone})`;
  }
  return {
    title: adminStr(cfg, 'pricesPreviewTitle', 'Priser'),
    titleSuffix,
    typeColumnSr: adminStr(cfg, 'pricesTicketTypeCol', 'Biljettyp'),
    note: adminStr(cfg, 'pricesPreviewHint', ''),
    dash: '—',
    tickets: payload.ticket_types,
    categories: payload.categories,
  };
}

export function adminPricePreviewCfg(cfg: AdminClientConfig): PriceCfg {
  return {
    priceDash: '—',
    priceDayTitle: adminStr(cfg, 'pricesPreviewType', 'Dagskort'),
    priceAfternoonReturnLabel: adminStr(cfg, 'pricesAfternoonHeading', 'Eftermiddags-retur'),
    priceAfternoonNote: adminStr(cfg, 'pricesAfternoonHint', ''),
  };
}
