<script setup lang="ts">
import { computed } from 'vue';
import MrtAccentButton from '../../components/ui/MrtAccentButton.vue';
import MrtPriceTable from '../../components/ui/MrtPriceTable.vue';
import MrtStepHeader from '../../components/ui/MrtStepHeader.vue';
import MrtSurfaceCard from '../../components/ui/MrtSurfaceCard.vue';
import MrtStepPanel from '../../components/ui/MrtStepPanel.vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { useSummaryExport } from '../composables/useSummaryExport';
import { useSummaryPrices } from '../composables/useSummaryPrices';
import { cfgStr, cfgStringArray } from '../utils/wizardLabels';
import { formatYmdForDisplay } from '../utils/wizardDate';
import WizardConnectionLeg from './WizardConnectionLeg.vue';

const { store, cfg, config } = useWizardContext();

const dateText = computed(() =>
  formatYmdForDisplay(store.dateYmd, cfgStringArray(cfg.value, 'monthNames')),
);

const backLabel = computed(() => cfgStr(cfg, 'back', '← Tillbaka'));
const ticketUrl = computed(() => (config.ticketUrl || '').trim());
const ticketCtaLabel = computed(() => cfgStr(cfg, 'ticketCta', 'Mer information om biljettköp'));

const { pricesLoading, priceData, dayPrices, priceLabels } = useSummaryPrices();

const {
  tripTypeLabel,
  printLabel,
  downloadPdfLabel,
  pdfDownloading,
  pdfError,
  onPrint,
  onDownloadPdf,
} = useSummaryExport({ dateText, priceData, dayPrices, priceLabels });

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

    <MrtSurfaceCard box class="mrt-journey-wizard__step-section">
      <div class="mrt-summary-print-root" data-wizard-summary-print>
        <p class="mrt-summary-print-title">{{ cfgStr(cfg, 'stepSummary', 'Din resa') }}</p>
        <p class="mrt-summary-print-meta">{{ tripTypeLabel }} · {{ store.contextLine.replace(/\n/g, ' · ') }}</p>

        <div
          class="mrt-summary-list"
          :class="{ 'mrt-summary-list--round-trip': store.tripType === 'return' && store.inbound }"
        >
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

<style scoped>
.mrt-summary-list {
  display: grid;
  gap: 1rem;
  grid-template-columns: 1fr;
}

.mrt-summary-list--round-trip {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.mrt-actions {
  text-align: center;
}

.mrt-summary-step-head {
  margin-bottom: 0.15rem;
}

:deep(.mrt-price-block) {
  margin-top: 1.25rem;
  padding-top: 1.25rem;
  border-top: 1px solid var(--mrt-color-neutral-300, #ccc);
}

:deep(.mrt-price-block + .mrt-price-block) {
  margin-top: 1rem;
  padding-top: 0;
  border-top: 0;
}

:deep(.mrt-price-block__note) {
  color: var(--mrt-color-neutral-700, #444);
}

.mrt-summary-print-title,
.mrt-summary-print-meta {
  display: none;
}

.mrt-summary-actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 0.75rem;
  margin-top: 1.75rem;
}

.mrt-summary-actions :deep(.mrt-accent-btn) {
  display: inline-flex;
  min-width: min(100%, 14rem);
  min-height: 2.85rem;
  padding: 0.5rem 1.25rem;
  font-size: 1.05rem;
}

.mrt-summary-actions__feedback {
  margin: 0.75rem 0 0;
  text-align: center;
  font-size: 0.95rem;
  color: var(--mrt-color-text-muted, #555);
}

@media (max-width: 48rem) {
  :deep(.mrt-price-list) {
    padding: 0;
    border-radius: 0;
    background: transparent;
  }

  :deep(.mrt-price-list__row) {
    padding: 0.55rem 0;
    border-bottom-color: var(--mrt-color-neutral-300, #ccc);
  }

  :deep(.mrt-price-list__label),
  :deep(.mrt-price-list__value) {
    color: var(--mrt-wizard-text, #151515);
    font-weight: 700;
  }

  :deep(.mrt-price-block__title) {
    font-size: 1.05rem;
  }

  :deep(.mrt-summary-card__heading) {
    font-size: 0.9rem;
  }
}
</style>

<style>
html.mrt-print-summary #mrt-print-clone {
  display: none;
}

@media print {
  @page {
    margin: 14mm 12mm;
    size: A4 portrait;
  }

  html.mrt-print-summary body > *:not(#mrt-print-clone) {
    display: none !important;
  }

  html.mrt-print-summary #mrt-print-clone {
    display: block !important;
    position: static;
    visibility: visible;
    left: auto;
    overflow: visible;
    margin: 0;
    padding: 0;
    background: #fff;
    color: #000;
    font-family: var(--mrt-font-body);
    font-size: 9.5pt;
    line-height: 1.35;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-summary-print-title {
    display: block;
    margin: 0 0 2mm;
    font-family: var(--mrt-font-heading);
    font-size: 14pt;
    font-weight: var(--mrt-font-weight-heading-strong);
  }

  html.mrt-print-summary #mrt-print-clone .mrt-summary-print-meta {
    display: block;
    margin: 0 0 5mm;
    padding-bottom: 3mm;
    border-bottom: 1px solid #ccc;
    font-size: 9pt;
    color: #333;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-summary-list {
    display: grid;
    gap: 3mm;
    margin: 0 0 4mm;
    grid-template-columns: 1fr;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-summary-list--round-trip {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  html.mrt-print-summary #mrt-print-clone .mrt-summary-card {
    break-inside: avoid;
    page-break-inside: avoid;
    margin: 0;
    padding: 3mm 4mm;
    border: 1px solid #bbb;
    border-radius: 0;
    box-shadow: none;
    background: #fff;
    color: #000;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-summary-card__heading {
    margin: 0 0 1.5mm;
    font-size: 8.5pt;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #1f4d2e;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-trip-summary__time {
    margin: 0 0 1mm;
    font-size: 11pt;
    font-weight: 700;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-trip-summary__route,
  html.mrt-print-summary #mrt-print-clone .mrt-trip-summary__date {
    margin: 0 0 1mm;
    font-size: 9pt;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-connection-leg-list {
    margin-top: 2mm;
    padding-top: 2mm;
    border-top: 1px dotted #ccc;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-connection-leg-list__icon,
  html.mrt-print-summary #mrt-print-clone .mrt-connection-leg-list__icon-fallback {
    display: none;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-connection-leg-list__leg {
    grid-template-columns: minmax(0, 1fr);
  }

  html.mrt-print-summary #mrt-print-clone .mrt-connection-leg-list__vehicle {
    font-size: 8.5pt;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-connection-leg-list__time {
    font-size: 9pt;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-connection-leg-list__route {
    font-size: 8pt;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-connection-leg-list__transfer {
    font-size: 8pt;
    background: #fff9c4;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-price-block {
    break-inside: avoid;
    page-break-inside: avoid;
    margin: 0;
    padding: 4mm 0 0;
    border-top: 1px solid #ccc;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-price-block__title {
    margin: 0 0 2mm;
    font-size: 9.5pt;
    font-weight: 700;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-price-columns--split {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 3mm;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-price-column__title {
    margin: 0 0 1.5mm;
    font-size: 8.5pt;
    font-weight: 700;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-price-list {
    display: table;
    width: 100%;
    border-collapse: collapse;
    margin: 0;
    padding: 0;
    background: transparent;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-price-list__row {
    display: table-row;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-price-list__label,
  html.mrt-print-summary #mrt-print-clone .mrt-price-list__value {
    display: table-cell;
    margin: 0;
    padding: 1mm 0;
    border-bottom: 1px solid #eee;
    font-size: 8.5pt;
    vertical-align: top;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-price-list__value {
    text-align: right;
    white-space: nowrap;
    width: 28%;
    font-weight: 700;
  }

  html.mrt-print-summary #mrt-print-clone .mrt-price-block__note {
    margin: 2mm 0 0;
    font-size: 8pt;
    color: #444;
  }
}
</style>
