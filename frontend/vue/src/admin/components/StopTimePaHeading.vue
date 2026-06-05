<script setup lang="ts">
import { computed } from 'vue';
import { adminConfig } from '../types';
import { adminStr } from '../utils/adminLabels';

const props = defineProps<{
  kind: 'pickup' | 'dropoff';
}>();

const cfg = adminConfig();

const shortLabel = computed(() =>
  props.kind === 'pickup'
    ? adminStr(cfg, 'stopTimesColPickup')
    : adminStr(cfg, 'stopTimesColDropoff'),
);

const tooltip = computed(() =>
  props.kind === 'pickup'
    ? adminStr(cfg, 'stopTimesPickupLabel')
    : adminStr(cfg, 'stopTimesDropoffLabel'),
);
</script>

<template>
  <abbr class="mrt-stop-pa-heading" :title="tooltip">{{ shortLabel }}</abbr>
</template>

<style scoped>
.mrt-stop-pa-heading {
  text-decoration: none;
  cursor: help;
  border-bottom: 1px dotted currentColor;
}
</style>
