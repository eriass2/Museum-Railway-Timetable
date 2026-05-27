<script setup lang="ts">
import { cfgStr } from '../wizard/utils/wizardLabels';
import type { MaybeRef } from 'vue';
import type { WizardCfg } from '../wizard/utils/wizardLabels';

defineProps<{
  cfg: MaybeRef<WizardCfg>;
  contextLine: string;
  title?: string;
}>();

const emit = defineEmits<{ back: [] }>();
</script>

<template>
  <header class="mrt-journey-wizard__step-head">
    <button
      type="button"
      class="mrt-journey-wizard__back"
      @click="emit('back')"
    >
      {{ cfgStr(cfg, 'back', '← Tillbaka') }}
    </button>
    <p class="mrt-journey-wizard__context">{{ contextLine }}</p>
  </header>
  <h3 v-if="title" class="mrt-journey-wizard__step-title">{{ title }}</h3>
  <slot />
</template>
