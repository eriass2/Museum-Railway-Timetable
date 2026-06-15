import { formatPriceCell, priceKeysFromMap } from '../../shared/prices';
import type { DayTicketData, TripPriceData } from '../../shared/prices';
import type { PriceTableLabels } from '../../shared/priceLabels';
import type { PriceCfg } from '../../shared/priceTypes';
import type { WizardStore } from '../store/wizardStoreTypes';
import {
  connectionLegItems,
  connectionRouteText,
  connectionTimeRange,
  type ConnectionLegContext,
} from '../composables/useConnectionLegDisplay';
import type { JourneyConnection } from '../types';
import type { WizardCfg } from './wizardCfgTypes';
import { cfgStr } from './wizardLabels';
import type { TripSummaryLeg, TripSummaryTextInput } from './tripSummaryText';

export type BuildTripSummaryInputParams = {
  store: WizardStore;
  cfg: WizardCfg;
  dateText: string;
  tripTypeLabel: string;
  priceData: TripPriceData | null;
  dayPrices: DayTicketData | null;
  priceLabels: PriceTableLabels;
};

function summaryLeg(
  conn: JourneyConnection,
  legCtx: ConnectionLegContext,
  heading: string,
  dateText: string,
  store: WizardStore,
  cfg: WizardCfg,
): TripSummaryLeg {
  return {
    heading,
    route: connectionRouteText(legCtx, store.fromTitle, store.toTitle),
    timeRange: connectionTimeRange(conn),
    date: dateText,
    segments: connectionLegItems(conn, store.config.stations || [], cfg),
  };
}

export function buildTripSummaryLegs(
  store: WizardStore,
  cfg: WizardCfg,
  dateText: string,
): TripSummaryLeg[] {
  const legs: TripSummaryLeg[] = [];
  if (store.outbound) {
    legs.push(
      summaryLeg(store.outbound, 'outbound', cfgStr(cfg, 'outboundHeading', 'Utresa'), dateText, store, cfg),
    );
  }
  if (store.tripType === 'return' && store.inbound) {
    legs.push(
      summaryLeg(store.inbound, 'return', cfgStr(cfg, 'returnHeading', 'Återresa'), dateText, store, cfg),
    );
  }
  return legs;
}

function buildPriceRows(
  data: TripPriceData,
  priceLabels: PriceTableLabels,
  cfg: PriceCfg,
): { label: string; value: string }[] {
  const ticketType = data.activeType;
  return priceKeysFromMap(priceLabels.categories).map((key) => ({
    label: priceLabels.categories[key] || key,
    value: formatPriceCell(data.matrix[ticketType]?.[key], {
      ...cfg,
      priceDash: priceLabels.dash,
    }),
  })).filter((row) => row.value && row.value !== '—');
}

function buildDayPriceRows(
  dayPrices: DayTicketData,
  priceLabels: PriceTableLabels,
  cfg: PriceCfg,
): { label: string; value: string }[] {
  if (!dayPrices.day) {
    return [];
  }
  return priceKeysFromMap(priceLabels.categories).map((key) => ({
    label: priceLabels.categories[key] || key,
    value: formatPriceCell(dayPrices.day![key], {
      ...cfg,
      priceDash: priceLabels.dash,
    }),
  })).filter((row) => row.value && row.value !== '—');
}

function buildPriceSectionNotes(
  priceData: TripPriceData,
  priceLabels: PriceTableLabels,
  priceCfg: PriceCfg,
): string[] {
  const lines: string[] = [];
  const zone = priceLabels.note.trim();
  if (zone) {
    lines.push(zone);
  }
  const senior = priceLabels.seniorNote?.trim() ?? '';
  if (senior) {
    lines.push(senior);
  }
  const purchase = priceLabels.stationPurchaseNote?.trim() ?? '';
  if (purchase) {
    lines.push(purchase);
  }
  for (const footnote of priceLabels.footnotes ?? []) {
    const text = footnote.trim();
    if (text) {
      lines.push(text);
    }
  }
  if (priceData.isAfternoonReturn && priceCfg.priceAfternoonNote) {
    lines.push(priceCfg.priceAfternoonNote);
  }
  return lines;
}

/** Plain-text/PDF input from wizard store + loaded prices. */
export function buildTripSummaryInput(params: BuildTripSummaryInputParams): TripSummaryTextInput {
  const { store, cfg, dateText, tripTypeLabel, priceData, dayPrices, priceLabels } = params;
  const priceCfg = cfg as PriceCfg;
  const ticketTypeLabel = priceData
    ? priceData.isAfternoonReturn
      ? priceCfg.priceAfternoonReturnLabel || priceLabels.tickets.return || 'Returbiljett'
      : priceLabels.tickets[priceData.activeType] || priceData.activeType
    : '';

  const legs = buildTripSummaryLegs(store, cfg, dateText);
  const routeName =
    store.fromTitle && store.toTitle
      ? `${store.fromTitle} → ${store.toTitle}`
      : legs[0]?.route ?? cfgStr(cfg, 'stepSummary', 'Din resa');
  const downloadName = store.dateYmd ? `${routeName} ${store.dateYmd}` : routeName;

  return {
    title: cfgStr(cfg, 'stepSummary', 'Din resa'),
    downloadName,
    tripTypeLabel,
    legs,
    priceSection: priceData
      ? {
          heading: cfgStr(cfg, 'summaryPricesHeading', 'Priser'),
          ticketTypeLabel,
          rows: buildPriceRows(priceData, priceLabels, priceCfg),
          notes: buildPriceSectionNotes(priceData, priceLabels, priceCfg),
          dayTicketHeading: dayPrices
            ? priceCfg.priceDayTitle || priceLabels.tickets.day || 'Heldagsbiljett'
            : undefined,
          dayTicketRows: dayPrices ? buildDayPriceRows(dayPrices, priceLabels, priceCfg) : [],
        }
      : undefined,
  };
}
