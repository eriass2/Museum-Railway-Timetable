import { computed, ref, type ComputedRef, type Ref } from 'vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { PRICE_CAT_KEYS, formatPriceCell } from '../../shared/prices';
import type { DayTicketData, TripPriceData } from '../../shared/prices';
import type { PriceTableLabels } from '../../shared/priceLabels';
import type { PriceCfg } from '../../shared/priceTypes';
import { printElement } from '../../utils/printElement';
import {
  connectionLegItems,
  connectionRouteText,
  connectionTimeRange,
} from './useConnectionLegDisplay';
import { downloadTripSummaryPdf } from '../utils/downloadTripSummaryPdf';
import type { WizardStore } from '../store/wizardStoreTypes';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import { cfgStr } from '../utils/wizardLabels';
import type { JourneyConnection } from '../types';
import type { TripSummaryLeg, TripSummaryTextInput } from '../utils/tripSummaryText';

type SummaryExportOptions = {
  dateText: ComputedRef<string>;
  priceData: Ref<TripPriceData | null>;
  dayPrices: Ref<DayTicketData | null>;
  priceLabels: ComputedRef<PriceTableLabels>;
};

function summaryLeg(
  conn: JourneyConnection,
  legCtx: 'outbound' | 'return',
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

function buildLegs(store: WizardStore, cfg: ComputedRef<WizardCfg>, dateText: string): TripSummaryLeg[] {
  const legs: TripSummaryLeg[] = [];
  if (store.outbound) {
    legs.push(
      summaryLeg(
        store.outbound,
        'outbound',
        cfgStr(cfg, 'outboundHeading', 'Utresa'),
        dateText,
        store,
        cfg.value,
      ),
    );
  }
  if (store.tripType === 'return' && store.inbound) {
    legs.push(
      summaryLeg(
        store.inbound,
        'return',
        cfgStr(cfg, 'returnHeading', 'Återresa'),
        dateText,
        store,
        cfg.value,
      ),
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
  return PRICE_CAT_KEYS.map((key) => ({
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
  return PRICE_CAT_KEYS.map((key) => ({
    label: priceLabels.categories[key] || key,
    value: formatPriceCell(dayPrices.day![key], {
      ...cfg,
      priceDash: priceLabels.dash,
    }),
  })).filter((row) => row.value && row.value !== '—');
}

function buildSummaryInput(
  store: WizardStore,
  cfg: ComputedRef<WizardCfg>,
  dateText: string,
  tripTypeLabel: string,
  priceData: TripPriceData | null,
  dayPrices: DayTicketData | null,
  priceLabels: PriceTableLabels,
): TripSummaryTextInput {
  const priceCfg = cfg.value as PriceCfg;
  const ticketTypeLabel = priceData
    ? priceData.isAfternoonReturn
      ? priceCfg.priceAfternoonReturnLabel || priceLabels.tickets.return || 'Returbiljett'
      : priceLabels.tickets[priceData.activeType] || priceData.activeType
    : '';

  return {
    title: cfgStr(cfg, 'stepSummary', 'Din resa'),
    tripTypeLabel,
    legs: buildLegs(store, cfg, dateText),
    priceSection: priceData
      ? {
          heading: cfgStr(cfg, 'summaryPricesHeading', 'Priser'),
          ticketTypeLabel,
          rows: buildPriceRows(priceData, priceLabels, priceCfg),
          note: priceData.isAfternoonReturn ? priceCfg.priceAfternoonNote : priceLabels.note,
          dayTicketHeading: dayPrices
            ? priceCfg.priceDayTitle || priceLabels.tickets.day || 'Heldagsbiljett'
            : undefined,
          dayTicketRows: dayPrices ? buildDayPriceRows(dayPrices, priceLabels, priceCfg) : [],
        }
      : undefined,
  };
}

export function useSummaryExport(options: SummaryExportOptions) {
  const { store, cfg, config } = useWizardContext();
  const pdfDownloading = ref(false);
  const pdfError = ref('');

  const tripTypeLabel = computed(() =>
    store.tripType === 'return'
      ? cfgStr(cfg, 'tripReturn', 'Tur och retur')
      : cfgStr(cfg, 'tripSingle', 'Enkel resa'),
  );

  const printLabel = computed(() => cfgStr(cfg, 'summaryPrint', 'Skriv ut'));
  const downloadPdfLabel = computed(() => cfgStr(cfg, 'summaryDownloadPdf', 'Ladda ner som PDF'));
  const pdfErrorLabel = computed(() =>
    cfgStr(cfg, 'summaryPdfError', 'Kunde inte skapa PDF. Försök igen eller använd Skriv ut.'),
  );

  function onPrint(): void {
    printElement('[data-wizard-summary-print]');
  }

  async function onDownloadPdf(): Promise<void> {
    if (pdfDownloading.value) {
      return;
    }
    pdfError.value = '';
    pdfDownloading.value = true;
    try {
      const input = buildSummaryInput(
        store,
        cfg,
        options.dateText.value,
        tripTypeLabel.value,
        options.priceData.value,
        options.dayPrices.value,
        options.priceLabels.value,
      );
      const ok = await downloadTripSummaryPdf(input, {
        tripPdfUrl: config.tripPdfUrl,
      });
      if (!ok) {
        pdfError.value = pdfErrorLabel.value;
      }
    } catch {
      pdfError.value = pdfErrorLabel.value;
    } finally {
      pdfDownloading.value = false;
    }
  }

  return {
    tripTypeLabel,
    printLabel,
    downloadPdfLabel,
    pdfDownloading,
    pdfError,
    onPrint,
    onDownloadPdf,
  };
}
