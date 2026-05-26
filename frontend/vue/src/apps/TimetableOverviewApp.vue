<script setup lang="ts">
import ExperimentShell from '../components/ExperimentShell.vue';
import type { MrtVueConfig } from '../useMrtConfig';

const props = defineProps<{ config: MrtVueConfig }>();
</script>

<template>
  <ExperimentShell
    title="Tidtabellsöversikt"
    app="overview"
    :config="props.config"
  />
  <p v-if="config.timetableId" class="mrt-vue-experiment__meta">
    Timetable ID: {{ config.timetableId }}
  </p>
</template>
