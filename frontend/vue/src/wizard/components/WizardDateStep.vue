<script setup lang="ts">
import { computed } from 'vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { useWizardCalendar } from '../composables/useWizardCalendar';
import WizardCalendarGrid from './WizardCalendarGrid.vue';
import WizardCalendarLegend from './WizardCalendarLegend.vue';
import WizardCalendarNav from './WizardCalendarNav.vue';
import WizardStepHeader from './WizardStepHeader.vue';
import WizardSurfaceCard from './WizardSurfaceCard.vue';
import { cfgStr } from '../utils/wizardLabels';

const { store, cfg, config } = useWizardContext();

const {
  loading,
  monthTitle,
  weekdayHeaders,
  gridRows,
  dayAria,
  hasBookableDays,
  onPickDate,
  shiftMonth,
  goToday,
} = useWizardCalendar(store, config, cfg);

const backLabel = computed(() => cfgStr(cfg, 'back', '← Tillbaka'));
const showEmptyMonth = computed(() => !loading.value && !hasBookableDays.value);

function onBack(): void {
  store.dateYmd = '';
  store.goTo('route');
}
</script>

<template>
  <div
    data-wizard-step="date"
    class="mrt-journey-wizard__panel mrt-journey-wizard__panel--active"
    role="region"
    :aria-label="cfgStr(cfg, 'stepDate', 'Välj datum')"
  >
    <WizardStepHeader :back-label="backLabel" :context-line="store.contextLine" @back="onBack" />

    <WizardSurfaceCard class="mrt-journey-wizard__calendar-card">
      <WizardCalendarNav
        :cfg="cfg"
        :month-title="monthTitle"
        @prev="shiftMonth(-1)"
        @next="shiftMonth(1)"
        @today="goToday"
      />
      <WizardCalendarGrid
        :loading="loading"
        :weekday-headers="weekdayHeaders"
        :grid-rows="gridRows"
        :selected-ymd="store.dateYmd"
        :grid-label="cfgStr(cfg, 'calendarGridLabel', '')"
        :loading-label="cfgStr(cfg, 'loading', 'Laddar...')"
        :day-aria="dayAria"
        @pick="onPickDate"
      />
      <p v-if="showEmptyMonth" class="mrt-journey-wizard__calendar-empty" role="status">
        {{ cfgStr(cfg, 'calendarEmptyMonth', 'Inga bokningsbara dagar denna månad för din resa.') }}
        <span class="mrt-journey-wizard__calendar-empty-hint">
          {{ cfgStr(cfg, 'calendarEmptyHint', 'Byt månad med pilarna ovan.') }}
        </span>
      </p>
      <WizardCalendarLegend :cfg="cfg" />
    </WizardSurfaceCard>
  </div>
</template>
