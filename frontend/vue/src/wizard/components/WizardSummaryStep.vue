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
  .mrt-summary-list--round-trip {
    grid-template-columns: 1fr;
  }

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
@import '../styles/wizardSummaryPrint.css';
</style>
