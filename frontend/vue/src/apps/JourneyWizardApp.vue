<script setup lang="ts">
import { computed } from 'vue';
import ExperimentShell from '../components/ExperimentShell.vue';
import type { MrtVueConfig } from '../useMrtConfig';

const props = defineProps<{ config: MrtVueConfig }>();

const stationCount = computed(() => {
  const list = props.config.stations;
  return Array.isArray(list) ? list.length : 0;
});
</script>

<template>
  <ExperimentShell
    title="Reseplanerare (journey wizard)"
    app="wizard"
    :config="props.config"
  />
  <ul class="mrt-vue-experiment__meta">
    <li>{{ stationCount }} stationer i config</li>
    <li v-if="config.debug">Debug: {{ config.debug }}</li>
    <li v-if="config.embedded">Embedded layout</li>
  </ul>
</template>
