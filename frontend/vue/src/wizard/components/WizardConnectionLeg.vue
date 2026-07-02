<script setup lang="ts">
import { computed, onMounted } from 'vue';
import MrtConnectionLegList from '../../components/ui/MrtConnectionLegList.vue';
import MrtSummaryCard from '../../components/ui/MrtSummaryCard.vue';
import MrtTripFootnotes from '../../components/ui/MrtTripFootnotes.vue';
import MrtTripSummary from '../../components/ui/MrtTripSummary.vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { tripFootnotesFromStops } from '../../shared/stopTimeFootnotes';
import type { ConnectionLegContext } from '../composables/useConnectionLegDisplay';
import { useConnectionLegDisplay } from '../composables/useConnectionLegDisplay';
import { useConnectionDetail } from '../composables/useConnectionDetail';
import { shouldShowConnectionLegList } from '../../shared/connectionLegDisplay';
import type { JourneyConnection } from '../types';

const props = defineProps<{
  connection: JourneyConnection;
  legCtx: ConnectionLegContext;
  heading: string;
  date: string;
}>();

const { store, cfg, config } = useWizardContext();

const { routeText, timeRange, legItems } = useConnectionLegDisplay(
  () => props.connection,
  props.legCtx,
);

const legFrom = computed(() => (props.legCtx === 'return' ? store.toId : store.fromId));
const legTo = computed(() => (props.legCtx === 'return' ? store.fromId : store.toId));
const dateYmd = computed(() => store.dateYmd);

const { segments, ensureLoaded } = useConnectionDetail({
  config,
  cfg,
  connection: props.connection,
  legFrom,
  legTo,
  dateYmd,
});

const tripFootnotes = computed(() =>
  tripFootnotesFromStops(
    segments.value.flatMap((seg) => seg.stops),
    cfg.value,
  ),
);

onMounted(() => {
  void ensureLoaded();
});
</script>

<template>
  <MrtSummaryCard :heading="heading">
    <MrtTripSummary :time-range="timeRange" :route="routeText" :date="date" />
    <MrtConnectionLegList v-if="shouldShowConnectionLegList(legItems)" :items="legItems" />
    <MrtTripFootnotes :entries="tripFootnotes" />
  </MrtSummaryCard>
</template>
