<script setup lang="ts">
import MrtHeading from './MrtHeading.vue';

defineProps<{
  heading?: string;
}>();
</script>

<template>
  <article class="mrt-summary-card">
    <MrtHeading v-if="heading || $slots.heading" level="h4" size="md" class="mrt-summary-card__heading">
      <slot name="heading">{{ heading }}</slot>
    </MrtHeading>
    <slot />
  </article>
</template>

<style scoped>
.mrt-summary-card {
  container-type: inline-size;
  container-name: mrt-summary-card;
  background: var(--mrt-wizard-surface, #fff);
  color: var(--mrt-wizard-text, #151515);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.14);
  padding: 0.75rem 0.9rem;
}

.mrt-summary-card__heading {
  color: var(--mrt-wizard-green-dark, #1f4d2e);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

@container mrt-summary-card (max-width: var(--mrt-max-narrow)) {
  .mrt-summary-card__heading {
    font-size: 0.9rem;
  }
}
</style>
