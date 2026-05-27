<script setup lang="ts">
import { ref, watch } from 'vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { todayYearMonth } from '../utils/wizardDate';
import { cfgStr } from '../utils/wizardLabels';
import type { TripType } from '../types';
import type { WizardStation } from '../../config/types';

const props = defineProps<{
  stations: WizardStation[];
  heroSubtitle: string;
  timetableHtml: string;
  showTimetable: boolean;
}>();

const { store, cfg } = useWizardContext();

const fromId = ref(store.fromId || 0);
const toId = ref(store.toId || 0);
const tripType = ref<TripType>(store.tripType || 'single');

watch([fromId, toId, tripType], () => {
  if (store.error) {
    store.clearError();
  }
});

function onSearch(): void {
  if (!store.validateRoute(fromId.value, toId.value)) {
    return;
  }
  const fromStation = props.stations.find((s) => s.id === fromId.value);
  const toStation = props.stations.find((s) => s.id === toId.value);
  store.setRoute(
    fromId.value,
    toId.value,
    tripType.value,
    fromStation?.title || '',
    toStation?.title || '',
  );
  const now = todayYearMonth();
  store.calYear = now.year;
  store.calMonth = now.month;
  store.goTo('date');
}
</script>

<template>
  <div
    data-wizard-step="route"
    class="mrt-journey-wizard__panel mrt-journey-wizard__panel--active mrt-journey-wizard__search-panel"
    :class="{ 'mrt-journey-wizard__search-panel--with-timetable': showTimetable }"
    role="region"
  >
    <header class="mrt-journey-wizard__hero-head">
      <h2 class="mrt-journey-wizard__hero-title">
        {{ cfgStr(cfg, 'routeTitle', 'Sök din resa med Lennakatten') }}
      </h2>
      <p v-if="heroSubtitle" class="mrt-journey-wizard__hero-lede">{{ heroSubtitle }}</p>
    </header>
    <div class="mrt-form-fields mrt-journey-wizard__route">
      <div class="mrt-form-field">
        <label for="mrt_wizard_from">{{ cfgStr(cfg, 'from', 'Från') }}</label>
        <select id="mrt_wizard_from" v-model.number="fromId" required>
          <option :value="0">{{ cfgStr(cfg, 'fromPlaceholder', '') }}</option>
          <option v-for="s in stations" :key="s.id" :value="s.id">{{ s.title }}</option>
        </select>
      </div>
      <div class="mrt-form-field">
        <label for="mrt_wizard_to">{{ cfgStr(cfg, 'to', 'Till') }}</label>
        <select id="mrt_wizard_to" v-model.number="toId" required>
          <option :value="0">{{ cfgStr(cfg, 'toPlaceholder', '') }}</option>
          <option v-for="s in stations" :key="'t-' + s.id" :value="s.id">{{ s.title }}</option>
        </select>
      </div>
      <fieldset class="mrt-form-field mrt-journey-wizard__trip-type">
        <legend class="mrt-sr-only">{{ cfgStr(cfg, 'tripTypeLegend', 'Restyp') }}</legend>
        <div class="mrt-journey-wizard__trip-type-toggle">
          <label class="mrt-journey-wizard__radio-label">
            <input v-model="tripType" type="radio" value="single">
            <span class="mrt-journey-wizard__radio-text" aria-hidden="true">→</span>
            <span class="mrt-journey-wizard__radio-text">{{ cfgStr(cfg, 'tripSingle', 'Enkel') }}</span>
          </label>
          <label class="mrt-journey-wizard__radio-label">
            <input v-model="tripType" type="radio" value="return">
            <span class="mrt-journey-wizard__radio-text" aria-hidden="true">↔</span>
            <span class="mrt-journey-wizard__radio-text">{{ cfgStr(cfg, 'tripReturn', 'Tur- och retur') }}</span>
          </label>
        </div>
      </fieldset>
      <div class="mrt-form-field mrt-journey-wizard__actions">
        <button type="button" class="mrt-btn mrt-btn--primary mrt-journey-wizard__cta" @click="onSearch">
          {{ cfgStr(cfg, 'searchTrip', 'Sök resa') }}
        </button>
      </div>
    </div>
    <details v-if="showTimetable && timetableHtml" class="mrt-journey-wizard__timetable">
      <summary class="mrt-journey-wizard__timetable-summary">
        {{ cfgStr(cfg, 'showTimetable', 'Visa tidtabell') }}
      </summary>
      <div class="mrt-journey-wizard__timetable-body" v-html="timetableHtml" />
    </details>
  </div>
</template>
