<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import MrtAccentButton from '../../components/ui/MrtAccentButton.vue';
import MrtAlert from '../../components/ui/MrtAlert.vue';
import MrtSegmentedControl from '../../components/ui/MrtSegmentedControl.vue';
import MrtHeading from '../../components/ui/MrtHeading.vue';
import MrtRouteLayout from '../../components/ui/MrtRouteLayout.vue';
import MrtStepPanel from '../../components/ui/MrtStepPanel.vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { todayYearMonth } from '../utils/wizardDate';
import { cfgStr } from '../utils/wizardLabels';
import type { TripType } from '../types';
import type { WizardStation } from '../../config/types';
import WizardStationField from './WizardStationField.vue';
import WizardTripTypeIcon from './WizardTripTypeIcon.vue';

const props = defineProps<{
  stations: WizardStation[];
  timetablePageUrl: string;
}>();

const { store, cfg } = useWizardContext();

const fromId = ref(store.fromId || 0);
const toId = ref(store.toId || 0);
const tripType = ref<TripType>(store.tripType || 'single');

const tripOptions = computed(() => [
  { value: 'single' as const, label: cfgStr(cfg, 'tripSingle', 'Enkel resa') },
  { value: 'return' as const, label: cfgStr(cfg, 'tripReturn', 'Tur och retur') },
]);

watch([fromId, toId, tripType], () => {
  if (store.error) {
    store.clearError();
  }
});

function stationTitle(id: number): string {
  return props.stations.find((s) => s.id === id)?.title || '';
}

function onSearch(): void {
  if (!store.validateRoute(fromId.value, toId.value)) {
    return;
  }
  store.setRoute(
    fromId.value,
    toId.value,
    tripType.value,
    stationTitle(fromId.value),
    stationTitle(toId.value),
  );
  const now = todayYearMonth();
  store.calYear = now.year;
  store.calMonth = now.month;
  store.goTo('date');
}
</script>

<template>
  <MrtStepPanel
    step="route"
    variant="search"
    :ariaLabel="cfgStr(cfg, 'stepRoute', 'Sök resa')"
  >
    <MrtHeading level="h2" size="xl" variant="surface-title">
      {{ cfgStr(cfg, 'routeTitle', 'Planera resa') }}
    </MrtHeading>

    <div class="mrt-journey-wizard__route-form">
      <MrtSegmentedControl
        v-model="tripType"
        size="compact"
        :legend="cfgStr(cfg, 'tripTypeLegend', 'Restyp')"
        :options="tripOptions"
      >
        <template #option="{ option }">
          <WizardTripTypeIcon :variant="option.value" />
          {{ option.label }}
        </template>
      </MrtSegmentedControl>

      <MrtRouteLayout
        link-tone="dark"
        :timetable-href="timetablePageUrl || undefined"
        :timetable-label="cfgStr(cfg, 'timetablePageLink', 'Visa hela tidtabellen')"
      >
        <template #stations>
          <div class="mrt-journey-wizard__station-field">
            <MrtHeading level="h3" size="sm">
              {{ cfgStr(cfg, 'from', 'Från') }}
            </MrtHeading>
            <WizardStationField
              id="mrt_wizard_from"
              v-model="fromId"
              hide-label
              :label="cfgStr(cfg, 'from', 'Från')"
              :placeholder="cfgStr(cfg, 'fromPlaceholder', 'Sök eller välj station…')"
              :search-aria="cfgStr(cfg, 'stationSearchAria', 'Sök avgångsstation')"
              :stations="stations"
              :exclude-id="toId"
            />
          </div>
          <div class="mrt-journey-wizard__station-field">
            <MrtHeading level="h3" size="sm">
              {{ cfgStr(cfg, 'to', 'Till') }}
            </MrtHeading>
            <WizardStationField
              id="mrt_wizard_to"
              v-model="toId"
              hide-label
              :label="cfgStr(cfg, 'to', 'Till')"
              :placeholder="cfgStr(cfg, 'toPlaceholder', 'Sök eller välj station…')"
              :search-aria="cfgStr(cfg, 'stationSearchAriaTo', 'Sök ankomststation')"
              :stations="stations"
              :exclude-id="fromId"
            />
          </div>
        </template>
      </MrtRouteLayout>

      <div v-if="store.error" class="mrt-field-error">
        <MrtAlert variant="error" live="assertive">{{ store.error }}</MrtAlert>
      </div>

      <div class="mrt-actions">
        <MrtAccentButton type="button" size="search" @click="onSearch">
          {{ cfgStr(cfg, 'searchTrip', 'Sök resa') }}
        </MrtAccentButton>
      </div>
    </div>
  </MrtStepPanel>
</template>

<style scoped>
.mrt-journey-wizard__route-form :deep(.mrt-segmented-field__legend) {
  color: inherit;
}

.mrt-journey-wizard__station-field {
  display: grid;
  gap: 0.25rem;
}

.mrt-field-error {
  margin: 0 0 1rem;
  text-align: center;
}

.mrt-actions {
  display: flex;
  justify-content: center;
  margin-top: 0.25rem;
}
</style>
