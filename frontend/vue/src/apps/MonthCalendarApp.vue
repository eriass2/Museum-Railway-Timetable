<script setup lang="ts">
import ExperimentShell from '../components/ExperimentShell.vue';
import type { MrtVueConfig } from '../useMrtConfig';

const props = defineProps<{ config: MrtVueConfig }>();
</script>

<template>
  <ExperimentShell
    title="Månadskalender"
    app="month"
    :config="props.config"
  />
  <p v-if="config.monthTitle" class="mrt-vue-experiment__meta">
    {{ config.monthTitle }} · {{ config.daysInMonth }} dagar
  </p>
</template>
