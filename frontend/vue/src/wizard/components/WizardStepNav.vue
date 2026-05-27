<script setup lang="ts">
import { computed } from 'vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { cfgStr } from '../utils/wizardLabels';

const { store, cfg } = useWizardContext();

const items = computed(() =>
  store.stepSequence.map((key, i) => ({
    key,
    label: `${i + 1}. ${store.stepLabels[key]}`,
    active: store.step === key,
    done: store.stepSequence.indexOf(store.step) > i,
  })),
);
</script>

<template>
  <nav class="mrt-journey-wizard__nav" :aria-label="cfgStr(cfg, 'stepNavAria', 'Trip planner steps')">
    <ol class="mrt-journey-wizard__steps">
      <li
        v-for="item in items"
        :key="item.key"
        :class="{ 'is-active': item.active, 'is-done': item.done }"
        :aria-current="item.active ? 'step' : undefined"
      >
        {{ item.label }}
      </li>
    </ol>
  </nav>
</template>
