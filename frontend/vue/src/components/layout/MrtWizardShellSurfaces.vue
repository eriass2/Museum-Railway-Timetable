<script setup lang="ts">
</script>

<template>
  <div class="mrt-wizard-shell-surfaces">
    <slot />
  </div>
</template>

<style scoped>
.mrt-wizard-shell-surfaces {
  --mrt-wizard-errors-max-width: var(--mrt-max-step);
  --mrt-step-panel-search-margin-top: clamp(1rem, 2.5vw, 1.75rem);
  --mrt-step-panel-search-padding-block: clamp(2rem, 4vw, 3.25rem);
  --mrt-step-panel-search-padding-inline: clamp(1.75rem, 4vw, 3rem);
}

@media (min-width: 48.0625rem) {
  .mrt-wizard-shell-surfaces {
    --mrt-wizard-errors-max-width: none;
    --mrt-step-panel-width: 100%;
    --mrt-step-panel-max-width: none;
    --mrt-step-panel-bg: transparent;
    --mrt-step-panel-search-width: 100%;
    --mrt-step-panel-wide-width: 100%;
  }
}

@media (max-width: 48rem) {
  .mrt-wizard-shell-surfaces {
    --mrt-step-panel-width: 100%;
    --mrt-step-panel-padding: 0.75rem 0.85rem 1.1rem;
    --mrt-step-panel-search-width: 100%;
    --mrt-step-panel-wide-width: 100%;
    --mrt-step-panel-search-padding-inline: 1rem;
  }
}
</style>
