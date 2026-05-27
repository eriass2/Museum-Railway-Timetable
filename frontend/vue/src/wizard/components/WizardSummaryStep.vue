<script setup lang="ts">
import { computed, inject } from 'vue';
import { wizardKey } from '../injection';
import { cfgStr } from '../utils/wizardLabels';
import { formatYmdForDisplay } from '../utils/wizardDate';
import { arrivalAtDestination, departureFromOrigin } from '../utils/connection';
import { formatTripClock } from '../utils/format';
import WizardPriceTable from './WizardPriceTable.vue';

const props = defineProps<{ ticketUrl: string }>();

const wizard = inject(wizardKey);
if (!wizard) {
  throw new Error('WizardSummaryStep requires wizard context');
}

const dateText = computed(() =>
  formatYmdForDisplay(wizard.dateYmd.value, wizard.cfg.monthNames as string[] | undefined),
);

function onBack(): void {
  wizard.clearError();
  if (wizard.tripType.value === 'return') {
    wizard.goTo('return');
  } else {
    wizard.goTo('outbound');
  }
}
</script>

<template>
  <div class="mrt-jw-panel mrt-journey-wizard__panel mrt-jw-panel--active mrt-journey-wizard__panel--active" role="region">
    <header class="mrt-jw-step-head mrt-journey-wizard__step-head">
      <button type="button" class="mrt-jw-btn mrt-jw-btn--back mrt-journey-wizard__back" @click="onBack">
        {{ cfgStr(wizard.cfg, 'back', '← Tillbaka') }}
      </button>
      <p class="mrt-jw-step-head__context mrt-journey-wizard__context">{{ wizard.contextLine }}</p>
    </header>
    <h3 class="mrt-jw-typo mrt-jw-typo--step-title mrt-journey-wizard__step-title">
      {{ cfgStr(wizard.cfg, 'stepSummary', 'Din resa') }}
    </h3>

    <div class="mrt-journey-wizard__summary-list">
      <article v-if="wizard.outbound" class="mrt-jw-card mrt-jw-card--summary mrt-journey-wizard__summary-card">
        <h4 class="mrt-jw-card__section-title mrt-journey-wizard__summary-heading">
          {{ cfgStr(wizard.cfg, 'outboundHeading', 'Utresa') }}
        </h4>
        <p class="mrt-jw-typo mrt-jw-typo--time">
          {{ formatTripClock(departureFromOrigin(wizard.outbound)) }} –
          {{ formatTripClock(arrivalAtDestination(wizard.outbound)) }}
        </p>
        <p class="mrt-jw-typo mrt-jw-typo--route">{{ wizard.fromTitle }} → {{ wizard.toTitle }}</p>
        <p class="mrt-jw-typo mrt-jw-typo--date">{{ dateText }}</p>
      </article>

      <article
        v-if="wizard.tripType === 'return' && wizard.inbound"
        class="mrt-jw-card mrt-jw-card--summary mrt-journey-wizard__summary-card"
      >
        <h4 class="mrt-jw-card__section-title mrt-journey-wizard__summary-heading">
          {{ cfgStr(wizard.cfg, 'returnHeading', 'Återresa') }}
        </h4>
        <p class="mrt-jw-typo mrt-jw-typo--time">
          {{ formatTripClock(departureFromOrigin(wizard.inbound)) }} –
          {{ formatTripClock(arrivalAtDestination(wizard.inbound)) }}
        </p>
        <p class="mrt-jw-typo mrt-jw-typo--route">{{ wizard.toTitle }} → {{ wizard.fromTitle }}</p>
        <p class="mrt-jw-typo mrt-jw-typo--date">{{ dateText }}</p>
      </article>
    </div>

    <WizardPriceTable
      :cfg="wizard.cfg"
      :trip-type="wizard.tripType"
      :from-id="wizard.fromId"
      :to-id="wizard.toId"
    />

    <p v-if="ticketUrl" class="mrt-mt-sm">
      <a
        :href="ticketUrl"
        class="mrt-jw-btn mrt-jw-btn--cta mrt-btn mrt-btn--primary mrt-journey-wizard__cta"
      >
        {{ cfgStr(wizard.cfg, 'ticketCta', 'Fortsätt till biljetter') }}
      </a>
    </p>
  </div>
</template>
