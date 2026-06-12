<script setup lang="ts">
import { computed, onMounted, watch } from 'vue';
import MrtAsyncState from '../../components/ui/MrtAsyncState.vue';
import MrtStepHeader from '../../components/ui/MrtStepHeader.vue';
import MrtSurfaceCard from '../../components/ui/MrtSurfaceCard.vue';
import MrtSelectedTrip from '../../components/ui/MrtSelectedTrip.vue';
import MrtStepPanel from '../../components/ui/MrtStepPanel.vue';
import MrtTripList from '../../components/ui/MrtTripList.vue';
import MrtTripSummary from '../../components/ui/MrtTripSummary.vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { useConnectionLegDisplay } from '../composables/useConnectionLegDisplay';
import { useTripConnections } from '../composables/useTripConnections';
import { cfgStr } from '../utils/wizardLabels';
import type { JourneyConnection } from '../types';
import WizardTripCard from './WizardTripCard.vue';

const props = defineProps<{
  legCtx: 'outbound' | 'return';
}>();

const wizardCtx = useWizardContext();
const { store, cfg } = wizardCtx;
const { loading, error, connections, loadConnections } = useTripConnections(
  wizardCtx,
  props.legCtx,
);

const { routeText: selectedOutboundRoute, timeRange: selectedOutboundTime } =
  useConnectionLegDisplay(() => store.outbound, 'outbound');

const stepLabel = computed(() =>
  props.legCtx === 'outbound'
    ? cfgStr(cfg, 'stepOutbound', 'Välj utresa')
    : cfgStr(cfg, 'stepReturn', 'Välj återresa'),
);

const backLabel = computed(() => cfgStr(cfg, 'back', '← Tillbaka'));

function onBack(): void {
  store.clearError();
  if (props.legCtx === 'outbound') {
    store.outbound = null;
    store.inbound = null;
    store.goTo('date');
  } else {
    store.inbound = null;
    store.goTo('outbound');
  }
}

function onSelect(conn: JourneyConnection): void {
  if (props.legCtx === 'outbound') {
    store.selectOutbound(conn);
  } else {
    store.selectInbound(conn);
  }
}

onMounted(() => void loadConnections());

watch(
  () => store.step,
  (s) => {
    if (s === props.legCtx) {
      void loadConnections();
    }
  },
);
</script>

<template>
  <MrtStepPanel :step="legCtx" variant="wide" :ariaLabel="stepLabel">
    <MrtStepHeader :back-label="backLabel" :context-line="store.contextLine" @back="onBack" />

    <MrtSurfaceCard box class="mrt-journey-wizard__step-section">
      <MrtSelectedTrip
        v-if="legCtx === 'return' && store.outbound"
        return-summary
      >
        <template #label>
          {{ cfgStr(cfg, 'selectedOutbound', 'Vald utresa') }}
        </template>
        <MrtTripSummary
          :time-range="selectedOutboundTime"
          :route="selectedOutboundRoute"
        />
      </MrtSelectedTrip>

      <MrtAsyncState
        :loading="loading && !connections.length"
        :error="error"
        :loading-text="cfgStr(cfg, 'loading', 'Laddar...')"
        :empty="!connections.length"
        :empty-text="cfgStr(cfg, 'noConnections', 'Inga anslutningar detta datum.')"
      >
        <MrtTripList>
          <WizardTripCard
            v-for="(conn, idx) in connections"
            :key="idx"
            :connection="conn"
            :leg-ctx="legCtx"
            @select="onSelect(conn)"
          />
        </MrtTripList>
      </MrtAsyncState>
    </MrtSurfaceCard>
  </MrtStepPanel>
</template>
