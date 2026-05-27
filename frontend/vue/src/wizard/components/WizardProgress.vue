<script setup lang="ts">
import { computed } from 'vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { cfgStr } from '../utils/wizardLabels';

const { store, cfg } = useWizardContext();

const items = computed(() => {
  const currentIndex = store.stepSequence.indexOf(store.step);
  return store.stepSequence.map((key, i) => ({
    key,
    label: `${i + 1}. ${store.stepLabels[key]}`,
    active: store.step === key,
    done: currentIndex > i,
  }));
});
</script>

<template>
  <nav class="mrt-journey-wizard__nav" :aria-label="cfgStr(cfg, 'stepNavAria', 'Steg i reseplaneraren')">
    <ol class="mrt-journey-wizard__progress" role="list">
      <li
        v-for="item in items"
        :key="item.key"
        class="mrt-journey-wizard__progress-step"
        :class="{ 'is-active': item.active, 'is-done': item.done }"
        :aria-current="item.active ? 'step' : undefined"
        :tabindex="item.active ? -1 : undefined"
      >
        <span class="mrt-journey-wizard__progress-label">{{ item.label }}</span>
      </li>
    </ol>
  </nav>
</template>
