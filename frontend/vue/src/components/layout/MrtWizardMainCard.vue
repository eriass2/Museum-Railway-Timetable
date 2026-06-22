<script setup lang="ts">
</script>

<template>
  <div class="mrt-wizard-main-card">
    <slot />
  </div>
</template>

<style scoped>
.mrt-wizard-main-card {
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  padding: clamp(1.25rem, 3vw, 2rem);
  background: var(--mrt-wizard-green-dark, #1e4d6b);
  color: #fff;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.22);
  --mrt-step-panel-width: 100%;
  --mrt-step-panel-max-width: none;
  --mrt-step-panel-margin-inline: 0;
  --mrt-step-panel-margin-top: 0;
  --mrt-step-panel-padding: 0;
  --mrt-step-panel-bg: transparent;
  --mrt-step-panel-color: inherit;
  --mrt-step-panel-search-width: 100%;
  --mrt-step-panel-wide-width: 100%;
  --mrt-step-panel-search-bg: var(--mrt-wizard-surface, #ffffff);
  --mrt-step-panel-search-color: var(--mrt-wizard-text, #1a1a1a);
  --mrt-wizard-beta-max-width: none;
  --mrt-step-nav-margin-bottom: 1.25rem;
  --mrt-heading-surface-color: #fff;
}
</style>
