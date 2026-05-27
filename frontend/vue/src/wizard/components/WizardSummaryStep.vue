<script setup lang="ts">
import { computed } from 'vue';
import { useWizardContext } from '../../composables/useWizardContext';
import MrtStepShell from '../../components/MrtStepShell.vue';
import { cfgStr } from '../utils/wizardLabels';
import { formatYmdForDisplay } from '../utils/wizardDate';
import { arrivalAtDestination, departureFromOrigin } from '../utils/connection';
import { formatTripClock } from '../utils/format';
import WizardPriceTable from './WizardPriceTable.vue';

const { ticketUrl } = defineProps<{ ticketUrl: string }>();

const { store, cfg } = useWizardContext();

const dateText = computed(() =>
  formatYmdForDisplay(store.dateYmd, cfg.value.monthNames as string[] | undefined),
);

const stepTitle = computed(() => cfgStr(cfg, 'stepSummary', 'Din resa'));

function onBack(): void {
  store.clearError();
  store.goTo(store.tripType === 'return' ? 'return' : 'outbound');
}
</script>

<template>
  <div class="mrt-journey-wizard__panel mrt-journey-wizard__panel--active" role="region">
    <MrtStepShell :cfg="cfg" :context-line="store.contextLine" :title="stepTitle" @back="onBack" />

    <div class="mrt-journey-wizard__summary-list">
      <article v-if="store.outbound" class="mrt-journey-wizard__summary-card">
        <h4 class="mrt-journey-wizard__summary-heading">
          {{ cfgStr(cfg, 'outboundHeading', 'Utresa') }}
        </h4>
        <p class="mrt-journey-wizard__trip-time">
          {{ formatTripClock(departureFromOrigin(store.outbound)) }} –
          {{ formatTripClock(arrivalAtDestination(store.outbound)) }}
        </p>
        <p class="mrt-journey-wizard__trip-route">{{ store.fromTitle }} → {{ store.toTitle }}</p>
        <p class="mrt-journey-wizard__trip-date">{{ dateText }}</p>
      </article>

      <article
        v-if="store.tripType === 'return' && store.inbound"
        class="mrt-journey-wizard__summary-card"
      >
        <h4 class="mrt-journey-wizard__summary-heading">
          {{ cfgStr(cfg, 'returnHeading', 'Återresa') }}
        </h4>
        <p class="mrt-journey-wizard__trip-time">
          {{ formatTripClock(departureFromOrigin(store.inbound)) }} –
          {{ formatTripClock(arrivalAtDestination(store.inbound)) }}
        </p>
        <p class="mrt-journey-wizard__trip-route">{{ store.toTitle }} → {{ store.fromTitle }}</p>
        <p class="mrt-journey-wizard__trip-date">{{ dateText }}</p>
      </article>
    </div>

    <WizardPriceTable
      :cfg="cfg"
      :trip-type="store.tripType"
      :from-id="store.fromId"
      :to-id="store.toId"
    />

    <p v-if="ticketUrl" class="mrt-mt-sm">
      <a :href="ticketUrl" class="mrt-btn mrt-btn--primary mrt-journey-wizard__cta">
        {{ cfgStr(cfg, 'ticketCta', 'Fortsätt till biljetter') }}
      </a>
    </p>
  </div>
</template>
