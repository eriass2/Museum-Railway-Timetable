<script setup lang="ts">
import { computed } from 'vue';
import MrtCalendarNav from '../../components/ui/MrtCalendarNav.vue';
import MrtLegend from '../../components/ui/MrtLegend.vue';
import MrtStepHeader from '../../components/ui/MrtStepHeader.vue';
import MrtSurfaceCard from '../../components/ui/MrtSurfaceCard.vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { useWizardCalendar } from '../composables/useWizardCalendar';
import WizardCalendarGrid from './WizardCalendarGrid.vue';
import WizardPanel from './WizardPanel.vue';
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

const legendItems = computed(() => [
  {
    swatchClass: 'mrt-legend-swatch--ok',
    label: cfgStr(cfg, 'legendOk', 'Kan bokas för din resa'),
  },
  {
    swatchClass: 'mrt-legend-swatch--traffic',
    label: cfgStr(cfg, 'legendTraffic', 'Trafik, ej din resa'),
  },
  {
    swatchClass: 'mrt-legend-swatch--none',
    label: cfgStr(cfg, 'legendNone', 'Ingen trafik'),
  },
]);

function onBack(): void {
  store.dateYmd = '';
  store.goTo('route');
}
</script>

<template>
  <WizardPanel step="date" :aria-label="cfgStr(cfg, 'stepDate', 'Välj datum')">
    <MrtStepHeader :back-label="backLabel" :context-line="store.contextLine" @back="onBack" />

    <MrtSurfaceCard flush>
      <MrtCalendarNav
        :month-title="monthTitle"
        :prev-aria="cfgStr(cfg, 'calPrevAria', 'Föregående månad')"
        :next-aria="cfgStr(cfg, 'calNextAria', 'Nästa månad')"
        :today-label="cfgStr(cfg, 'goToToday', 'Idag')"
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
      <p v-if="showEmptyMonth" class="mrt-status-message" role="status">
        {{ cfgStr(cfg, 'calendarEmptyMonth', 'Inga bokningsbara dagar denna månad för din resa.') }}
        <span class="mrt-status-message__hint">
          {{ cfgStr(cfg, 'calendarEmptyHint', 'Byt månad med pilarna ovan.') }}
        </span>
      </p>
      <MrtLegend :items="legendItems" />
    </MrtSurfaceCard>
  </WizardPanel>
</template>
