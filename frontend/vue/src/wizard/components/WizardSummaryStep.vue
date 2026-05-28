<script setup lang="ts">
import { computed } from 'vue';
import MrtAccentButton from '../../components/ui/MrtAccentButton.vue';
import MrtPriceTable from '../../components/ui/MrtPriceTable.vue';
import MrtStepHeader from '../../components/ui/MrtStepHeader.vue';
import MrtSummaryCard from '../../components/ui/MrtSummaryCard.vue';
import MrtSurfaceCard from '../../components/ui/MrtSurfaceCard.vue';
import MrtTripSummary from '../../components/ui/MrtTripSummary.vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { cfgStr, cfgStringArray } from '../utils/wizardLabels';
import { priceTableLabelsFromCfg } from '../utils/priceTableLabels';
import { zonesForStationPair } from '../../shared/prices';
import { formatYmdForDisplay } from '../utils/wizardDate';
import { arrivalAtDestination, departureFromOrigin } from '../utils/connection';
import { formatTripClock } from '../utils/format';
import MrtStepPanel from '../../components/ui/MrtStepPanel.vue';

const { ticketUrl } = defineProps<{ ticketUrl: string }>();

const { store, cfg } = useWizardContext();

const dateText = computed(() =>
  formatYmdForDisplay(store.dateYmd, cfgStringArray(cfg.value, 'monthNames')),
);

const backLabel = computed(() => cfgStr(cfg, 'back', '← Tillbaka'));

const priceLabels = computed(() => {
  const zones = zonesForStationPair(store.fromId, store.toId, cfg.value);
  return priceTableLabelsFromCfg(cfg.value, zones, true);
});

function legTimeRange(conn: NonNullable<typeof store.outbound>): string {
  return `${formatTripClock(departureFromOrigin(conn))} – ${formatTripClock(arrivalAtDestination(conn))}`;
}

function onBack(): void {
  store.clearError();
  store.goTo(store.tripType === 'return' ? 'return' : 'outbound');
}
</script>

<template>
  <MrtStepPanel step="summary" variant="wide" :ariaLabel="cfgStr(cfg, 'stepSummary', 'Din resa')">
    <MrtStepHeader :back-label="backLabel" :context-line="store.contextLine" @back="onBack" />

    <MrtSurfaceCard>
      <div class="mrt-summary-list">
        <MrtSummaryCard v-if="store.outbound" :heading="cfgStr(cfg, 'outboundHeading', 'Utresa')">
          <MrtTripSummary
            :time-range="legTimeRange(store.outbound)"
            :route="`${store.fromTitle} → ${store.toTitle}`"
            :date="dateText"
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
        </MrtSummaryCard>
      </div>

      <MrtPriceTable
        :price-cfg="cfg"
        :labels="priceLabels"
        :trip-type="store.tripType"
        :from-id="store.fromId"
        :to-id="store.toId"
      />

      <p v-if="ticketUrl" data-wizard-ticket-wrap class="mrt-mt-sm mrt-actions">
        <MrtAccentButton :href="ticketUrl">
          {{ cfgStr(cfg, 'ticketCta', 'Fortsätt till biljetter') }}
        </MrtAccentButton>
      </p>
    </MrtSurfaceCard>
  </MrtStepPanel>
</template>
