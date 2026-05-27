<script setup lang="ts">
import { computed, inject, onMounted, ref, watch } from 'vue';
import { mrtPost } from '../../api/mrtApi';
import { wizardKey } from '../injection';
import type { JourneyConnection } from '../types';
import { cfgStr } from '../utils/wizardLabels';
import { arrivalAtDestination } from '../utils/connection';
import WizardTripCard from './WizardTripCard.vue';
import { formatTripClock } from '../utils/format';

const props = defineProps<{
  legCtx: 'outbound' | 'return';
}>();

const wizard = inject(wizardKey)!;

const loading = ref(false);
const error = ref('');
const connections = ref<JourneyConnection[]>([]);

const title = computed(() =>
  props.legCtx === 'outbound'
    ? cfgStr(wizard.cfg, 'stepOutbound', 'Välj utresa')
    : cfgStr(wizard.cfg, 'stepReturn', 'Välj återresa'),
);

const routeText = computed(() =>
  props.legCtx === 'return'
    ? `${wizard.toTitle.value} → ${wizard.fromTitle.value}`
    : `${wizard.fromTitle.value} → ${wizard.toTitle.value}`,
);

const legFrom = computed(() => (props.legCtx === 'return' ? wizard.toId.value : wizard.fromId.value));
const legTo = computed(() => (props.legCtx === 'return' ? wizard.fromId.value : wizard.toId.value));

const selectedOutbound = computed(() => wizard.outbound.value);

async function loadConnections(): Promise<void> {
  loading.value = true;
  error.value = '';
  connections.value = [];

  const mock =
    props.legCtx === 'outbound'
      ? wizard.debugOutboundConnections.value
      : wizard.debugReturnConnections.value;
  if (mock?.length) {
    connections.value = mock;
    loading.value = false;
    return;
  }

  const payload: Record<string, string | number> = {
    from_station: wizard.fromId.value,
    to_station: wizard.toId.value,
    date: wizard.dateYmd.value,
    trip_type: props.legCtx === 'return' ? 'return' : 'single',
  };

  if (props.legCtx === 'return' && wizard.outbound.value) {
    const arr = arrivalAtDestination(wizard.outbound.value);
    if (!arr) {
      loading.value = false;
      wizard.showError(cfgStr(wizard.cfg, 'errorGeneric', 'Error'));
      return;
    }
    payload.outbound_arrival = arr;
  }

  const res = await mrtPost<{ connections: JourneyConnection[] }>(
    wizard.config,
    'mrt_search_journey',
    payload,
  );
  loading.value = false;

  if (!res.success) {
    error.value = res.message || cfgStr(wizard.cfg, 'errorGeneric', 'Error');
    return;
  }
  connections.value = res.data?.connections || [];
}

function onSelect(conn: JourneyConnection): void {
  if (props.legCtx === 'outbound') {
    wizard.selectOutbound(conn);
  } else {
    wizard.selectInbound(conn);
  }
}

function onBack(): void {
  wizard.clearError();
  if (props.legCtx === 'outbound') {
    wizard.outbound.value = null;
    wizard.inbound.value = null;
    wizard.goTo('date');
  } else {
    wizard.inbound.value = null;
    wizard.goTo('outbound');
  }
}

onMounted(() => void loadConnections());

watch(
  () => wizard.step.value,
  (s) => {
    if (s === props.legCtx) {
      void loadConnections();
    }
  },
);
</script>

<template>
  <div class="mrt-jw-panel mrt-journey-wizard__panel mrt-jw-panel--active mrt-journey-wizard__panel--active" role="region">
    <header class="mrt-jw-step-head mrt-journey-wizard__step-head">
      <button type="button" class="mrt-jw-btn mrt-jw-btn--back mrt-journey-wizard__back" @click="onBack">
        {{ cfgStr(wizard.cfg, 'back', '← Tillbaka') }}
      </button>
      <p class="mrt-jw-step-head__context mrt-journey-wizard__context">{{ wizard.contextLine }}</p>
    </header>

    <div
      v-if="legCtx === 'return' && selectedOutbound"
      class="mrt-journey-wizard__selected-trip"
    >
      <div class="mrt-jw-selected-label mrt-journey-wizard__selected-label">
        {{ cfgStr(wizard.cfg, 'selectedOutbound', 'Vald utresa') }}
      </div>
      <div class="mrt-jw-card mrt-jw-card--selected mrt-journey-wizard__selected-card">
        <p class="mrt-jw-typo mrt-jw-typo--time">
          {{ formatTripClock(selectedOutbound.from_departure || '') }} –
          {{ formatTripClock(selectedOutbound.to_arrival || '') }}
        </p>
        <p class="mrt-jw-typo mrt-jw-typo--route">{{ wizard.fromTitle }} → {{ wizard.toTitle }}</p>
      </div>
    </div>

    <h3 class="mrt-jw-typo mrt-jw-typo--step-title mrt-journey-wizard__step-title">{{ title }}</h3>

    <p v-if="loading" class="mrt-empty">{{ cfgStr(wizard.cfg, 'loading', 'Loading...') }}</p>
    <div v-else-if="error" class="mrt-alert mrt-alert-error" role="alert">{{ error }}</div>
    <div v-else-if="!connections.length" class="mrt-alert mrt-alert-info">
      <p>{{ cfgStr(wizard.cfg, 'noConnections', 'No connections.') }}</p>
    </div>
    <div v-else class="mrt-jw-trip-list mrt-journey-wizard__trip-list">
      <WizardTripCard
        v-for="(conn, idx) in connections"
        :key="idx"
        :config="wizard.config"
        :cfg="wizard.cfg"
        :connection="conn"
        :leg-ctx="legCtx"
        :leg-from="legFrom"
        :leg-to="legTo"
        :route-text="routeText"
        @select="onSelect(conn)"
      />
    </div>
  </div>
</template>
