<script setup lang="ts">
defineProps<{
  /** Keeps return-step wizard overrides in journey-wizard CSS. */
  returnSummary?: boolean;
}>();
</script>

<template>
  <div
    class="mrt-selected-trip"
    :class="{ 'mrt-selected-trip--return-summary': returnSummary }"
  >
    <div class="mrt-selected-trip__label">
      <slot name="label" />
    </div>
    <div class="mrt-selected-trip__card">
      <slot />
    </div>
  </div>
</template>

<style scoped>
.mrt-selected-trip__label {
  margin-bottom: 0.4rem;
  color: var(--mrt-color-on-dark-link, #f5e6a8);
  font-weight: 700;
  font-size: 1rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.mrt-selected-trip__card {
  border: 3px solid var(--mrt-wizard-yellow);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.14);
  padding: 0.75rem 0.9rem;
}

.mrt-selected-trip--return-summary {
  margin: 0 0 1.5rem;
  padding-bottom: 1.35rem;
  border-bottom: 2px solid rgba(255, 255, 255, 0.35);
}

.mrt-selected-trip--return-summary .mrt-selected-trip__label {
  color: #ffffff;
}

.mrt-selected-trip--return-summary .mrt-selected-trip__card {
  border: 3px solid var(--mrt-wizard-yellow);
  background: var(--mrt-wizard-surface);
  color: var(--mrt-wizard-text);
}

@media (max-width: 48rem) {
  .mrt-selected-trip__card {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.85rem 0.75rem;
  }
}
</style>
