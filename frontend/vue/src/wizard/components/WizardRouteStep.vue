<script setup lang="ts">
import { ref, watch } from 'vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { todayYearMonth } from '../utils/wizardDate';
import { cfgStr } from '../utils/wizardLabels';
import type { TripType } from '../types';
import type { WizardStation } from '../../config/types';
import WizardAccentButton from './WizardAccentButton.vue';
import WizardStationField from './WizardStationField.vue';
import WizardSurfaceCard from './WizardSurfaceCard.vue';
import WizardTripTypeIcon from './WizardTripTypeIcon.vue';

const props = defineProps<{
  stations: WizardStation[];
  timetablePageUrl: string;
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
    role="region"
    :aria-label="cfgStr(cfg, 'stepRoute', 'Sök resa')"
  >
    <WizardSurfaceCard>
      <h2 class="mrt-journey-wizard__hero-title">
        {{ cfgStr(cfg, 'routeTitle', 'Planera resa med Lennakatten') }}
      </h2>

      <div class="mrt-journey-wizard__route">
        <div class="mrt-journey-wizard__route-stations">
          <WizardStationField
            id="mrt_wizard_from"
            v-model="fromId"
            :label="cfgStr(cfg, 'from', 'Från')"
            :placeholder="cfgStr(cfg, 'fromPlaceholder', 'Sök station…')"
            :search-aria="cfgStr(cfg, 'stationSearchAria', 'Sök avgångsstation')"
            :stations="stations"
            :exclude-id="toId"
          />
          <WizardStationField
            id="mrt_wizard_to"
            v-model="toId"
            :label="cfgStr(cfg, 'to', 'Till')"
            :placeholder="cfgStr(cfg, 'toPlaceholder', 'Sök station…')"
            :search-aria="cfgStr(cfg, 'stationSearchAriaTo', 'Sök ankomststation')"
            :stations="stations"
            :exclude-id="fromId"
          />
        </div>

        <fieldset class="mrt-journey-wizard__trip-type">
          <legend>{{ cfgStr(cfg, 'tripTypeLegend', 'Restyp') }}</legend>
          <div class="mrt-journey-wizard__trip-type-segmented" role="radiogroup">
            <button
              type="button"
              class="mrt-journey-wizard__trip-type-btn"
              role="radio"
              :aria-checked="tripType === 'single'"
              :class="{ 'is-active': tripType === 'single' }"
              @click="tripType = 'single'"
            >
              <WizardTripTypeIcon variant="single" />
              {{ cfgStr(cfg, 'tripSingle', 'Enkel resa') }}
            </button>
            <button
              type="button"
              class="mrt-journey-wizard__trip-type-btn"
              role="radio"
              :aria-checked="tripType === 'return'"
              :class="{ 'is-active': tripType === 'return' }"
              @click="tripType = 'return'"
            >
              <WizardTripTypeIcon variant="return" />
              {{ cfgStr(cfg, 'tripReturn', 'Tur och retur') }}
            </button>
          </div>
        </fieldset>

        <div class="mrt-journey-wizard__actions">
          <WizardAccentButton type="button" @click="onSearch">
            {{ cfgStr(cfg, 'searchTrip', 'Sök resa') }}
          </WizardAccentButton>
        </div>

        <p v-if="timetablePageUrl" class="mrt-journey-wizard__timetable-link-wrap">
          <a
            class="mrt-journey-wizard__timetable-link"
            :href="timetablePageUrl"
            target="_blank"
            rel="noopener noreferrer"
          >
            {{ cfgStr(cfg, 'timetablePageLink', 'Visa hela tidtabellen') }}
          </a>
        </p>
      </div>
    </WizardSurfaceCard>
  </div>
</template>
