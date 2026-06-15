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
  shareLabel,
  shareFeedback,
  shareFeedbackIsError,
  pdfDownloading,
  pdfError,
  onPrint,
  onDownloadPdf,
  onShare,
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
          context="summary"
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
          size="summary"
        >
          {{ ticketCtaLabel }}
        </MrtAccentButton>
        <MrtAccentButton type="button" variant="secondary" size="summary" @click="onPrint">
          {{ printLabel }}
        </MrtAccentButton>
        <MrtAccentButton type="button" variant="secondary" size="summary" @click="onShare">
          {{ shareLabel }}
        </MrtAccentButton>
        <MrtAccentButton
          type="button"
          variant="secondary"
          size="summary"
          :disabled="pdfDownloading"
          @click="onDownloadPdf"
        >
          {{ downloadPdfLabel }}
        </MrtAccentButton>
      </div>
      <p v-if="pdfError" class="mrt-summary-actions__feedback" role="alert">
        {{ pdfError }}
      </p>
      <p
        v-if="shareFeedback"
        class="mrt-summary-actions__feedback"
        :role="shareFeedbackIsError ? 'alert' : 'status'"
      >
        {{ shareFeedback }}
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
}
</style>

<style>
@import '../styles/wizardSummaryPrint.css';
</style>
