<script setup lang="ts">
import { computed, onMounted, watch } from 'vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { useTripConnections } from '../composables/useTripConnections';
import MrtStepShell from '../../components/MrtStepShell.vue';
import { cfgStr } from '../utils/wizardLabels';
import WizardTripCard from './WizardTripCard.vue';
import { formatTripClock } from '../utils/format';

const props = defineProps<{
  legCtx: 'outbound' | 'return';
}>();

const wizardCtx = useWizardContext();
const { store, cfg, config } = wizardCtx;
const { loading, error, connections, loadConnections } = useTripConnections(
  wizardCtx,
  props.legCtx,
);

const title = computed(() =>
  props.legCtx === 'outbound'
    ? cfgStr(cfg, 'stepOutbound', 'Välj utresa')
    : cfgStr(cfg, 'stepReturn', 'Välj återresa'),
);

const backLabel = computed(() => cfgStr(cfg, 'back', '← Tillbaka'));

const routeText = computed(() =>
  props.legCtx === 'return'
    ? `${store.toTitle} → ${store.fromTitle}`
    : `${store.fromTitle} → ${store.toTitle}`,
);

const legFrom = computed(() => (props.legCtx === 'return' ? store.toId : store.fromId));
const legTo = computed(() => (props.legCtx === 'return' ? store.fromId : store.toId));

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
  <div
    :data-wizard-step="legCtx"
    class="mrt-journey-wizard__panel mrt-journey-wizard__panel--active"
    role="region"
  >
    <MrtStepShell
      :back-label="backLabel"
      :context-line="store.contextLine"
      :title="title"
      @back="onBack"
    >
      <div
        v-if="legCtx === 'return' && store.outbound"
        data-wizard-return-summary
        class="mrt-journey-wizard__selected-trip"
      >
        <div class="mrt-journey-wizard__selected-label">
          {{ cfgStr(cfg, 'selectedOutbound', 'Vald utresa') }}
        </div>
        <div class="mrt-journey-wizard__selected-card">
          <p class="mrt-journey-wizard__trip-time">
            {{ formatTripClock(store.outbound.from_departure || '') }} –
            {{ formatTripClock(store.outbound.to_arrival || '') }}
          </p>
          <p class="mrt-journey-wizard__trip-route">{{ store.fromTitle }} → {{ store.toTitle }}</p>
        </div>
      </div>

      <p v-if="loading" class="mrt-empty">{{ cfgStr(cfg, 'loading', 'Laddar...') }}</p>
      <div v-else-if="error" class="mrt-alert mrt-alert-error" role="alert">{{ error }}</div>
      <div v-else-if="!connections.length" class="mrt-alert mrt-alert-info">
        <p>{{ cfgStr(cfg, 'noConnections', 'Inga anslutningar hittades.') }}</p>
      </div>
      <div v-else class="mrt-journey-wizard__trip-list">
        <WizardTripCard
          v-for="(conn, idx) in connections"
          :key="idx"
          :config="config"
          :cfg="cfg"
          :connection="conn"
          :leg-ctx="legCtx"
          :leg-from="legFrom"
          :leg-to="legTo"
          :route-text="routeText"
          @select="legCtx === 'outbound' ? store.selectOutbound(conn) : store.selectInbound(conn)"
        />
      </div>
    </MrtStepShell>
  </div>
</template>
