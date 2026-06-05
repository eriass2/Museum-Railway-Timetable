<script setup lang="ts">
import { computed } from 'vue';
import MrtAccentButton from '../../components/ui/MrtAccentButton.vue';
import MrtPriceTable from '../../components/ui/MrtPriceTable.vue';
import MrtStepHeader from '../../components/ui/MrtStepHeader.vue';
import MrtSurfaceCard from '../../components/ui/MrtSurfaceCard.vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { useSummaryExport } from '../composables/useSummaryExport';
import { useSummaryPrices } from '../composables/useSummaryPrices';
import { cfgStr, cfgStringArray } from '../utils/wizardLabels';
import { formatYmdForDisplay } from '../utils/wizardDate';
import MrtStepPanel from '../../components/ui/MrtStepPanel.vue';
import WizardConnectionLeg from './WizardConnectionLeg.vue';

const { store, cfg, config } = useWizardContext();

const dateText = computed(() =>
  formatYmdForDisplay(store.dateYmd, cfgStringArray(cfg.value, 'monthNames')),
);

const backLabel = computed(() => cfgStr(cfg, 'back', '← Tillbaka'));
const ticketUrl = computed(() => (config.ticketUrl || '').trim());
const ticketCtaLabel = computed(() => cfgStr(cfg, 'ticketCta', 'Fortsätt till biljetter'));

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
