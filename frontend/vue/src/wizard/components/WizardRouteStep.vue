<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import MrtAccentButton from '../../components/ui/MrtAccentButton.vue';
import MrtAlert from '../../components/ui/MrtAlert.vue';
import MrtSegmentedControl from '../../components/ui/MrtSegmentedControl.vue';
import MrtHeading from '../../components/ui/MrtHeading.vue';
import MrtRouteLayout from '../../components/ui/MrtRouteLayout.vue';
import MrtStepPanel from '../../components/ui/MrtStepPanel.vue';
import MrtSurfaceCard from '../../components/ui/MrtSurfaceCard.vue';
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
  <MrtStepPanel
    step="route"
    variant="search"
    :ariaLabel="cfgStr(cfg, 'stepRoute', 'Sök resa')"
  >
    <MrtSurfaceCard>
      <MrtHeading level="h2" size="xl" variant="surface-title">
        {{ cfgStr(cfg, 'routeTitle', 'Planera resa med Lennakatten') }}
      </MrtHeading>

      <MrtRouteLayout
        :timetable-href="timetablePageUrl || undefined"
        :timetable-label="cfgStr(cfg, 'timetablePageLink', 'Visa hela tidtabellen')"
      >
        <template #stations>
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
        </template>

        <MrtSegmentedControl
          v-model="tripType"
          :legend="cfgStr(cfg, 'tripTypeLegend', 'Restyp')"
          :options="tripOptions"
        >
          <template #option="{ option }">
            <WizardTripTypeIcon :variant="option.value" />
            {{ option.label }}
          </template>
        </MrtSegmentedControl>

        <div v-if="store.error" class="mrt-field-error">
          <MrtAlert variant="error" live="assertive">{{ store.error }}</MrtAlert>
        </div>

        <div class="mrt-actions">
          <MrtAccentButton type="button" @click="onSearch">
            {{ cfgStr(cfg, 'searchTrip', 'Sök resa') }}
          </MrtAccentButton>
        </div>
      </MrtRouteLayout>
    </MrtSurfaceCard>
  </MrtStepPanel>
</template>
