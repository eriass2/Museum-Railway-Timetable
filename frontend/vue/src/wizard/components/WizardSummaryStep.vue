<script setup lang="ts">
import { computed, ref } from 'vue';
import MrtAccentButton from '../../components/ui/MrtAccentButton.vue';
import MrtConnectionLegList from '../../components/ui/MrtConnectionLegList.vue';
import MrtPriceTable from '../../components/ui/MrtPriceTable.vue';
import MrtStepHeader from '../../components/ui/MrtStepHeader.vue';
import MrtSummaryCard from '../../components/ui/MrtSummaryCard.vue';
import MrtSurfaceCard from '../../components/ui/MrtSurfaceCard.vue';
import MrtTripSummary from '../../components/ui/MrtTripSummary.vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { useTripPrices } from '../../composables/useTripPrices';
import { cfgStr, cfgStringArray } from '../utils/wizardLabels';
import { priceTableLabelsFromCfg } from '../utils/priceTableLabels';
import { PRICE_CAT_KEYS, formatPriceCell } from '../../shared/prices';
import type { PriceCfg } from '../../shared/priceTypes';
import { formatYmdForDisplay } from '../utils/wizardDate';
import { arrivalAtDestination, departureFromOrigin } from '../utils/connection';
import {
  buildConnectionLegSummary,
} from '../utils/buildConnectionLegSummary';
import { stationTitleLookup } from '../../shared/connectionLegDisplay';
import { connectionToPriceLegs } from '../../shared/connectionPriceLegs';
import { formatTripClock } from '../utils/format';
import type { JourneyConnection } from '../types';
import MrtStepPanel from '../../components/ui/MrtStepPanel.vue';
import { printElement } from '../../utils/printElement';
import {
  buildTripSummaryText,
  canUseWebShare,
  copyTripSummaryText,
  shareTripSummaryText,
  type TripSummaryLeg,
} from '../utils/tripSummaryText';

const { store, cfg, config } = useWizardContext();
const shareFeedback = ref('');

const dateText = computed(() =>
  formatYmdForDisplay(store.dateYmd, cfgStringArray(cfg.value, 'monthNames')),
);

const backLabel = computed(() => cfgStr(cfg, 'back', '← Tillbaka'));
const printLabel = computed(() => cfgStr(cfg, 'summaryPrint', 'Skriv ut / spara som PDF'));
const shareLabel = computed(() =>
  canUseWebShare()
    ? cfgStr(cfg, 'summaryShare', 'Dela resa')
    : cfgStr(cfg, 'summaryCopy', 'Kopiera resa'),
);

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

function legTimeRange(conn: NonNullable<typeof store.outbound>): string {
  return `${formatTripClock(departureFromOrigin(conn))} – ${formatTripClock(arrivalAtDestination(conn))}`;
}

const outboundLegItems = computed(() =>
  store.outbound ? connectionLegItems(store.outbound) : [],
);
const inboundLegItems = computed(() =>
  store.inbound ? connectionLegItems(store.inbound) : [],
);

function connectionLegItems(conn: JourneyConnection) {
  const stationTitle = stationTitleLookup(store.config.stations || []);
  return buildConnectionLegSummary(conn, stationTitle, cfg.value);
}

function buildLegs(): TripSummaryLeg[] {
  const legs: TripSummaryLeg[] = [];
  if (store.outbound) {
    legs.push({
      heading: cfgStr(cfg, 'outboundHeading', 'Utresa'),
      route: `${store.fromTitle} → ${store.toTitle}`,
      timeRange: legTimeRange(store.outbound),
      date: dateText.value,
      segments: connectionLegItems(store.outbound),
    });
  }
  if (store.tripType === 'return' && store.inbound) {
    legs.push({
      heading: cfgStr(cfg, 'returnHeading', 'Återresa'),
      route: `${store.toTitle} → ${store.fromTitle}`,
      timeRange: legTimeRange(store.inbound),
      date: dateText.value,
      segments: connectionLegItems(store.inbound),
    });
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

function summaryPlainText(): string {
  const data = priceData.value;
  const priceCfg = cfg.value as PriceCfg;
  const ticketTypeLabel = data
    ? data.isAfternoonReturn
      ? priceCfg.priceAfternoonReturnLabel || priceLabels.value.tickets.return || 'Returbiljett'
      : priceLabels.value.tickets[data.activeType] || data.activeType
    : '';

  return buildTripSummaryText({
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
  });
}

function setShareFeedback(message: string): void {
  shareFeedback.value = message;
}

async function onShareOrCopy(): Promise<void> {
  const text = summaryPlainText();
  const title = cfgStr(cfg, 'stepSummary', 'Din resa');
  if (canUseWebShare()) {
    const result = await shareTripSummaryText(title, text);
    if (result === 'shared') {
      setShareFeedback(cfgStr(cfg, 'summaryShareDone', 'Resan delades.'));
      return;
    }
    if (result === 'aborted') {
      return;
    }
  }
  const copied = await copyTripSummaryText(text);
  setShareFeedback(
    copied
      ? cfgStr(cfg, 'summaryCopyDone', 'Resan kopierades till urklipp.')
      : cfgStr(cfg, 'summaryShareFailed', 'Kunde inte dela eller kopiera resan.'),
  );
}

function onPrint(): void {
  printElement('[data-wizard-summary-print]');
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
          <MrtSummaryCard v-if="store.outbound" :heading="cfgStr(cfg, 'outboundHeading', 'Utresa')">
            <MrtTripSummary
              :time-range="legTimeRange(store.outbound)"
              :route="`${store.fromTitle} → ${store.toTitle}`"
              :date="dateText"
            />
            <MrtConnectionLegList
              v-if="outboundLegItems.length"
              :items="outboundLegItems"
            />
          </MrtSummaryCard>

          <MrtSummaryCard
            v-if="store.tripType === 'return' && store.inbound"
            :heading="cfgStr(cfg, 'returnHeading', 'Återresa')"
          >
            <MrtTripSummary
              :time-range="legTimeRange(store.inbound)"
              :route="`${store.toTitle} → ${store.fromTitle}`"
              :date="dateText"
            />
            <MrtConnectionLegList
              v-if="inboundLegItems.length"
              :items="inboundLegItems"
            />
          </MrtSummaryCard>
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
        <MrtAccentButton type="button" variant="secondary" @click="onShareOrCopy">
          {{ shareLabel }}
        </MrtAccentButton>
      </div>
      <p
        v-if="shareFeedback"
        class="mrt-summary-actions__feedback"
        role="status"
        aria-live="polite"
      >
        {{ shareFeedback }}
      </p>
    </MrtSurfaceCard>
  </MrtStepPanel>
</template>
