<script setup lang="ts">
import MrtConnectionLegList from '../../components/ui/MrtConnectionLegList.vue';
import MrtSummaryCard from '../../components/ui/MrtSummaryCard.vue';
import MrtTripSummary from '../../components/ui/MrtTripSummary.vue';
import type { ConnectionLegContext } from '../composables/useConnectionLegDisplay';
import { useConnectionLegDisplay } from '../composables/useConnectionLegDisplay';
import type { JourneyConnection } from '../types';

const props = defineProps<{
  connection: JourneyConnection;
  legCtx: ConnectionLegContext;
  heading: string;
  date: string;
}>();

const { routeText, timeRange, legItems } = useConnectionLegDisplay(
  () => props.connection,
  props.legCtx,
);
</script>

<template>
  <MrtSummaryCard :heading="heading">
    <MrtTripSummary :time-range="timeRange" :route="routeText" :date="date" />
    <MrtConnectionLegList v-if="legItems.length" :items="legItems" />
  </MrtSummaryCard>
</template>
