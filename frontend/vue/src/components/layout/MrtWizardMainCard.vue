<script setup lang="ts">
defineProps<{
  embedded?: boolean;
}>();
</script>

<template>
  <div class="mrt-wizard-main-card" :class="{ 'mrt-wizard-main-card--embedded': embedded }">
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
}

:deep(.mrt-journey-wizard__beta) {
  max-width: none;
  margin-bottom: 1rem;
}

:deep(.mrt-step-nav) {
  margin-bottom: 1.25rem;
}

:deep(.mrt-wizard-main-card__errors) {
  max-width: none;
}

:deep(.mrt-heading--surface-title) {
  color: #fff;
}

:deep(.mrt-step-panel),
:deep(.mrt-step-panel--search),
:deep(.mrt-step-panel--wide) {
  width: 100%;
  max-width: none;
  margin-inline: 0;
  margin-top: 0;
  padding: 0;
  background: transparent;
  color: inherit;
}

:deep(.mrt-journey-wizard__route-form) {
  margin-top: 0.25rem;
}

:deep(.mrt-journey-wizard__step-section) {
  margin-top: 0.5rem;
}

.mrt-wizard-main-card--embedded {
  box-shadow: none;
  padding: 0;
}
</style>
