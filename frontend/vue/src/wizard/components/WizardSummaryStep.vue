<script setup lang="ts">
import { computed, ref } from 'vue';
import MrtAccentButton from '../../components/ui/MrtAccentButton.vue';
import MrtPriceTable from '../../components/ui/MrtPriceTable.vue';
import MrtStepHeader from '../../components/ui/MrtStepHeader.vue';
import MrtSurfaceCard from '../../components/ui/MrtSurfaceCard.vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { useTripPrices } from '../../composables/useTripPrices';
import { cfgStr, cfgStringArray } from '../utils/wizardLabels';
import { priceTableLabelsFromCfg } from '../utils/priceTableLabels';
import { PRICE_CAT_KEYS, formatPriceCell } from '../../shared/prices';
import type { PriceCfg } from '../../shared/priceTypes';
import { formatYmdForDisplay } from '../utils/wizardDate';
import { departureFromOrigin } from '../utils/connection';
import {
  connectionLegItems,
  connectionRouteText,
  connectionTimeRange,
} from '../composables/useConnectionLegDisplay';
import { connectionToPriceLegs } from '../../shared/connectionPriceLegs';
import type { JourneyConnection } from '../types';
import MrtStepPanel from '../../components/ui/MrtStepPanel.vue';
import WizardConnectionLeg from './WizardConnectionLeg.vue';
import { printElement } from '../../utils/printElement';
import { type TripSummaryLeg, type TripSummaryTextInput } from '../utils/tripSummaryText';
import { downloadTripSummaryPdf } from '../utils/downloadTripSummaryPdf';

const { store, cfg, config } = useWizardContext();

const dateText = computed(() =>
  formatYmdForDisplay(store.dateYmd, cfgStringArray(cfg.value, 'monthNames')),
);

const backLabel = computed(() => cfgStr(cfg, 'back', '← Tillbaka'));
const printLabel = computed(() => cfgStr(cfg, 'summaryPrint', 'Skriv ut'));
const downloadPdfLabel = computed(() => cfgStr(cfg, 'summaryDownloadPdf', 'Ladda ner som PDF'));
const pdfErrorLabel = computed(() =>
  cfgStr(cfg, 'summaryPdfError', 'Kunde inte skapa PDF. Försök igen eller använd Skriv ut.'),
);
const pdfDownloading = ref(false);
const pdfError = ref('');

const ticketUrl = computed(() => (config.ticketUrl || '').trim());
const ticketCtaLabel = computed(() => cfgStr(cfg, 'ticketCta', 'Fortsätt till biljetter'));

const outboundDeparture = computed(() =>
  store.outbound ? departureFromOrigin(store.outbound) : '',
);
const inboundDeparture = computed(() =>
  store.inbound ? departureFromOrigin(store.inbound) : '',
);

const tripPricesQuery = computed(() => ({
  fromId: store.fromId,
  toId: store.toId,
  tripType: store.tripType,
  outboundDeparture: outboundDeparture.value,
  inboundDeparture: inboundDeparture.value,
  includeDay: true,
  outboundLegs: store.outbound
    ? connectionToPriceLegs(store.outbound, store.fromId, store.toId)
    : [],
  inboundLegs:
    store.tripType === 'return' && store.inbound
      ? connectionToPriceLegs(store.inbound, store.toId, store.fromId)
      : [],
}));

const restConfig = computed(() => config);

const { loading: pricesLoading, zones, trip: priceData, day: dayPrices } = useTripPrices(
  restConfig,
  tripPricesQuery,
);

const priceLabels = computed(() =>
  priceTableLabelsFromCfg(
    cfg.value,
    zones.value,
    store.tripType === 'return' || !priceData.value?.isAfternoonReturn,
  ),
);

const tripTypeLabel = computed(() =>
  store.tripType === 'return'
    ? cfgStr(cfg, 'tripReturn', 'Tur och retur')
    : cfgStr(cfg, 'tripSingle', 'Enkel resa'),
);

function summaryLeg(
  conn: JourneyConnection,
  legCtx: 'outbound' | 'return',
  heading: string,
): TripSummaryLeg {
  return {
    heading,
    route: connectionRouteText(legCtx, store.fromTitle, store.toTitle),
    timeRange: connectionTimeRange(conn),
    date: dateText.value,
    segments: connectionLegItems(conn, store.config.stations || [], cfg.value),
  };
}

function buildLegs(): TripSummaryLeg[] {
  const legs: TripSummaryLeg[] = [];
  if (store.outbound) {
    legs.push(summaryLeg(store.outbound, 'outbound', cfgStr(cfg, 'outboundHeading', 'Utresa')));
  }
  if (store.tripType === 'return' && store.inbound) {
    legs.push(summaryLeg(store.inbound, 'return', cfgStr(cfg, 'returnHeading', 'Återresa')));
  }
  return legs;
}

function buildPriceRows(): { label: string; value: string }[] {
  const data = priceData.value;
  if (!data) {
    return [];
  }
  const ticketType = data.activeType;
  return PRICE_CAT_KEYS.map((key) => ({
    label: priceLabels.value.categories[key] || key,
    value: formatPriceCell(data.matrix[ticketType]?.[key], {
      ...cfg.value,
      priceDash: priceLabels.value.dash,
    }),
  })).filter((row) => row.value && row.value !== '—');
}

function buildDayPriceRows(): { label: string; value: string }[] {
  if (!dayPrices.value?.day) {
    return [];
  }
  return PRICE_CAT_KEYS.map((key) => ({
    label: priceLabels.value.categories[key] || key,
    value: formatPriceCell(dayPrices.value!.day![key], {
      ...cfg.value,
      priceDash: priceLabels.value.dash,
    }),
  })).filter((row) => row.value && row.value !== '—');
}

function buildSummaryInput(): TripSummaryTextInput {
  const data = priceData.value;
  const priceCfg = cfg.value as PriceCfg;
  const ticketTypeLabel = data
    ? data.isAfternoonReturn
      ? priceCfg.priceAfternoonReturnLabel || priceLabels.value.tickets.return || 'Returbiljett'
      : priceLabels.value.tickets[data.activeType] || data.activeType
    : '';

  return {
    title: cfgStr(cfg, 'stepSummary', 'Din resa'),
    tripTypeLabel: tripTypeLabel.value,
    legs: buildLegs(),
    priceSection: data
      ? {
          heading: cfgStr(cfg, 'summaryPricesHeading', 'Priser'),
          ticketTypeLabel,
          rows: buildPriceRows(),
          note: data.isAfternoonReturn ? priceCfg.priceAfternoonNote : priceLabels.value.note,
          dayTicketHeading: dayPrices.value
            ? priceCfg.priceDayTitle || priceLabels.value.tickets.day || 'Heldagsbiljett'
            : undefined,
          dayTicketRows: buildDayPriceRows(),
        }
      : undefined,
  };
}

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
    const ok = await downloadTripSummaryPdf(buildSummaryInput(), {
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

function onBack(): void {
  store.clearError();
  store.goTo(store.tripType === 'return' ? 'return' : 'outbound');
}
</script>

<template>
  <MrtStepPanel step="summary" variant="wide" :ariaLabel="cfgStr(cfg, 'stepSummary', 'Din resa')">
    <MrtStepHeader
      class="mrt-summary-step-head"
      :back-label="backLabel"
      :context-line="store.contextLine"
      @back="onBack"
    />

    <MrtSurfaceCard>
      <div class="mrt-summary-print-root" data-wizard-summary-print>
        <p class="mrt-summary-print-title">{{ cfgStr(cfg, 'stepSummary', 'Din resa') }}</p>
        <p class="mrt-summary-print-meta">{{ tripTypeLabel }} · {{ store.contextLine.replace(/\n/g, ' · ') }}</p>

        <div class="mrt-summary-list">
          <WizardConnectionLeg
            v-if="store.outbound"
            :connection="store.outbound"
            leg-ctx="outbound"
            :heading="cfgStr(cfg, 'outboundHeading', 'Utresa')"
            :date="dateText"
          />
          <WizardConnectionLeg
            v-if="store.tripType === 'return' && store.inbound"
            :connection="store.inbound"
            leg-ctx="return"
            :heading="cfgStr(cfg, 'returnHeading', 'Återresa')"
            :date="dateText"
          />
        </div>

        <MrtPriceTable
          :price-cfg="cfg"
          :labels="priceLabels"
          :trip-price="priceData"
          :day-price="dayPrices"
          :loading="pricesLoading"
        />
      </div>

      <div class="mrt-actions mrt-summary-actions">
        <MrtAccentButton
          v-if="ticketUrl"
          :href="ticketUrl"
          variant="primary"
        >
          {{ ticketCtaLabel }}
        </MrtAccentButton>
        <MrtAccentButton type="button" variant="secondary" @click="onPrint">
          {{ printLabel }}
        </MrtAccentButton>
        <MrtAccentButton
          type="button"
          variant="secondary"
          :disabled="pdfDownloading"
          @click="onDownloadPdf"
        >
          {{ downloadPdfLabel }}
        </MrtAccentButton>
      </div>
      <p v-if="pdfError" class="mrt-summary-actions__feedback" role="alert">
        {{ pdfError }}
      </p>
    </MrtSurfaceCard>
  </MrtStepPanel>
</template>
