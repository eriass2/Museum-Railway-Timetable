<script setup lang="ts">
import type { MaybeRef } from 'vue';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import { cfgStr } from '../utils/wizardLabels';

defineProps<{
  cfg: MaybeRef<WizardCfg>;
  monthTitle: string;
}>();

const emit = defineEmits<{
  prev: [];
  next: [];
  today: [];
}>();
</script>

<template>
  <div class="mrt-journey-wizard__calendar-nav">
    <button
      type="button"
      class="mrt-journey-wizard__cal-prev"
      :aria-label="cfgStr(cfg, 'calPrevAria', 'Föregående månad')"
      @click="emit('prev')"
    >
      ‹
    </button>
    <span class="mrt-journey-wizard__cal-title" aria-live="polite">{{ monthTitle }}</span>
    <button
      type="button"
      class="mrt-journey-wizard__cal-next"
      :aria-label="cfgStr(cfg, 'calNextAria', 'Nästa månad')"
      @click="emit('next')"
    >
      ›
    </button>
    <button type="button" class="mrt-journey-wizard__cal-today" @click="emit('today')">
      {{ cfgStr(cfg, 'goToToday', 'Idag') }}
    </button>
  </div>
</template>
