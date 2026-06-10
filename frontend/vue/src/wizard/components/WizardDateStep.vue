<script setup lang="ts">
import { computed } from 'vue';
import MrtCalendarNav from '../../components/ui/MrtCalendarNav.vue';
import MrtLegend from '../../components/ui/MrtLegend.vue';
import MrtStepHeader from '../../components/ui/MrtStepHeader.vue';
import MrtSurfaceCard from '../../components/ui/MrtSurfaceCard.vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { useWizardCalendar } from '../composables/useWizardCalendar';
import WizardCalendarGrid from './WizardCalendarGrid.vue';
import MrtStatusMessage from '../../components/ui/MrtStatusMessage.vue';
import MrtStepPanel from '../../components/ui/MrtStepPanel.vue';
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
    label: cfgStr(cfg, 'legendOk', 'Trafik för din resa'),
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
  <MrtStepPanel step="date" :ariaLabel="cfgStr(cfg, 'stepDate', 'Välj datum')">
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
      <MrtStatusMessage
        v-if="showEmptyMonth"
        :message="cfgStr(cfg, 'calendarEmptyMonth', 'Ingen trafik för din resa denna månad.')"
        :hint="cfgStr(cfg, 'calendarEmptyHint', 'Byt månad med pilarna ovan.')"
      />
      <MrtLegend :items="legendItems" />
    </MrtSurfaceCard>
  </MrtStepPanel>
</template>
