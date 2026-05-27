<script setup lang="ts">
import { computed, inject } from 'vue';
import { wizardKey } from '../injection';

const wizard = inject(wizardKey);
if (!wizard) {
  throw new Error('WizardStepNav requires wizard context');
}

const items = computed(() =>
  wizard.stepSequence.value.map((key, i) => ({
    key,
    label: `${i + 1}. ${wizard.stepLabels.value[key]}`,
    active: wizard.step.value === key,
    done: wizard.stepSequence.value.indexOf(wizard.step.value) > i,
  })),
);
</script>

<template>
  <nav class="mrt-journey-wizard__nav" :aria-label="String(wizard.cfg.stepNavAria || 'Trip planner steps')">
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
