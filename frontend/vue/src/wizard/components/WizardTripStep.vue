<script setup lang="ts">
import { computed, onMounted, watch } from 'vue';
import MrtAsyncState from '../../components/ui/MrtAsyncState.vue';
import MrtStepHeader from '../../components/ui/MrtStepHeader.vue';
import MrtSurfaceCard from '../../components/ui/MrtSurfaceCard.vue';
import MrtTripSummary from '../../components/ui/MrtTripSummary.vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { useTripConnections } from '../composables/useTripConnections';
import { cfgStr } from '../utils/wizardLabels';
import WizardPanel from './WizardPanel.vue';
import WizardTripCard from './WizardTripCard.vue';
import { formatTripClock } from '../utils/format';

const props = defineProps<{
  legCtx: 'outbound' | 'return';
}>();

const wizardCtx = useWizardContext();
const { store, cfg } = wizardCtx;
const { loading, error, connections, loadConnections } = useTripConnections(
  wizardCtx,
  props.legCtx,
);

const stepLabel = computed(() =>
  props.legCtx === 'outbound'
    ? cfgStr(cfg, 'stepOutbound', 'Välj utresa')
    : cfgStr(cfg, 'stepReturn', 'Välj återresa'),
);

const backLabel = computed(() => cfgStr(cfg, 'back', '← Tillbaka'));

const selectedOutboundTime = computed(() => {
  if (!store.outbound) {
    return '';
  }
  return `${formatTripClock(store.outbound.from_departure || '')} – ${formatTripClock(store.outbound.to_arrival || '')}`;
});

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
  <WizardPanel :step="legCtx" variant="wide" :ariaLabel="stepLabel">
    <MrtStepHeader :back-label="backLabel" :context-line="store.contextLine" @back="onBack" />

    <MrtSurfaceCard>
      <div
        v-if="legCtx === 'return' && store.outbound"
        data-wizard-return-summary
        class="mrt-journey-wizard__selected-trip"
      >
        <div class="mrt-journey-wizard__selected-label">
          {{ cfgStr(cfg, 'selectedOutbound', 'Vald utresa') }}
        </div>
        <div class="mrt-journey-wizard__selected-card">
          <MrtTripSummary
            :time-range="selectedOutboundTime"
            :route="`${store.fromTitle} → ${store.toTitle}`"
          />
        </div>
      </div>

      <MrtAsyncState
        :loading="loading"
        :error="error"
        :loading-text="cfgStr(cfg, 'loading', 'Laddar...')"
        :empty="!connections.length"
        :empty-text="cfgStr(cfg, 'noConnections', 'Inga anslutningar hittades.')"
      >
        <div class="mrt-journey-wizard__trip-list">
          <WizardTripCard
            v-for="(conn, idx) in connections"
            :key="idx"
            :connection="conn"
            :leg-ctx="legCtx"
            @select="legCtx === 'outbound' ? store.selectOutbound(conn) : store.selectInbound(conn)"
          />
        </div>
      </MrtAsyncState>
    </MrtSurfaceCard>
  </WizardPanel>
</template>
