<script setup lang="ts">
import { computed } from 'vue';
import { adminConfig } from '../../types';
import {
  stopTimePaShortLabel,
  stopTimePaTooltip,
} from '../../utils/timetable-editor/stopTimePaLabels';

const props = defineProps<{
  kind: 'pickup' | 'dropoff';
}>();

const cfg = adminConfig();

const shortLabel = computed(() => stopTimePaShortLabel(cfg, props.kind));
const tooltip = computed(() => stopTimePaTooltip(cfg, props.kind));
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
