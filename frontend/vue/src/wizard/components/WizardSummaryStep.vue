<script setup lang="ts">
import { computed } from 'vue';
import MrtAccentButton from '../../components/ui/MrtAccentButton.vue';
import MrtStepHeader from '../../components/ui/MrtStepHeader.vue';
import MrtSurfaceCard from '../../components/ui/MrtSurfaceCard.vue';
import MrtTripSummary from '../../components/ui/MrtTripSummary.vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { cfgStr, cfgStringArray } from '../utils/wizardLabels';
import { formatYmdForDisplay } from '../utils/wizardDate';
import { arrivalAtDestination, departureFromOrigin } from '../utils/connection';
import { formatTripClock } from '../utils/format';
import WizardPriceTable from './WizardPriceTable.vue';

const { ticketUrl } = defineProps<{ ticketUrl: string }>();

const { store, cfg } = useWizardContext();

const dateText = computed(() =>
  formatYmdForDisplay(store.dateYmd, cfgStringArray(cfg.value, 'monthNames')),
);

const backLabel = computed(() => cfgStr(cfg, 'back', '← Tillbaka'));

function legTimeRange(conn: NonNullable<typeof store.outbound>): string {
  return `${formatTripClock(departureFromOrigin(conn))} – ${formatTripClock(arrivalAtDestination(conn))}`;
}

function onBack(): void {
  store.clearError();
  store.goTo(store.tripType === 'return' ? 'return' : 'outbound');
}
</script>

<template>
  <div
    data-wizard-step="summary"
    class="mrt-journey-wizard__panel mrt-journey-wizard__panel--active"
    role="region"
    :aria-label="cfgStr(cfg, 'stepSummary', 'Din resa')"
  >
    <MrtStepHeader :back-label="backLabel" :context-line="store.contextLine" @back="onBack" />

    <MrtSurfaceCard>
      <div class="mrt-journey-wizard__summary-list">
        <article v-if="store.outbound" class="mrt-journey-wizard__summary-card">
          <h4 class="mrt-journey-wizard__summary-heading">
            {{ cfgStr(cfg, 'outboundHeading', 'Utresa') }}
          </h4>
          <MrtTripSummary
            :time-range="legTimeRange(store.outbound)"
            :route="`${store.fromTitle} → ${store.toTitle}`"
            :date="dateText"
          />
        </article>

        <article
          v-if="store.tripType === 'return' && store.inbound"
          class="mrt-journey-wizard__summary-card"
        >
          <h4 class="mrt-journey-wizard__summary-heading">
            {{ cfgStr(cfg, 'returnHeading', 'Återresa') }}
          </h4>
          <MrtTripSummary
            :time-range="legTimeRange(store.inbound)"
            :route="`${store.toTitle} → ${store.fromTitle}`"
            :date="dateText"
          />
        </article>
      </div>

      <WizardPriceTable
        :cfg="cfg"
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
  </div>
</template>
