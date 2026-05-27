<script setup lang="ts">
import { computed } from 'vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { useWizardCalendar } from '../composables/useWizardCalendar';
import MrtStepShell from '../../components/MrtStepShell.vue';
import WizardCalendarGrid from './WizardCalendarGrid.vue';
import WizardCalendarLegend from './WizardCalendarLegend.vue';
import WizardCalendarNav from './WizardCalendarNav.vue';
import { cfgStr } from '../utils/wizardLabels';

const { store, cfg, config } = useWizardContext();

const {
  loading,
  monthTitle,
  weekdayHeaders,
  gridRows,
  dayAria,
  onPickDate,
  shiftMonth,
  goToday,
} = useWizardCalendar(store, config, cfg);

const stepTitle = computed(() => cfgStr(cfg, 'stepDate', 'Välj datum'));
const backLabel = computed(() => cfgStr(cfg, 'back', '← Tillbaka'));

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
  >
    <MrtStepShell
      :back-label="backLabel"
      :context-line="store.contextLine"
      :title="stepTitle"
      @back="onBack"
    />
    <div class="mrt-journey-wizard__calendar-card">
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
      <WizardCalendarLegend :cfg="cfg" />
    </div>
  </div>
</template>
