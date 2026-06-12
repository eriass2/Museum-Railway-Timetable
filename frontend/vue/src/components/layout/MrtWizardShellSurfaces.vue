<script setup lang="ts">
defineProps<{
  embedded?: boolean;
  bleedBackground?: boolean;
}>();
</script>

<template>
  <div
    class="mrt-wizard-shell-surfaces"
    :class="{ 'mrt-wizard-shell-surfaces--embedded': embedded }"
  >
    <slot />
  </div>
</template>

<style scoped>
:deep(.mrt-wizard-main-card__errors) {
  max-width: 46rem;
  margin: 0 auto 0.75rem;
}

:deep(.mrt-journey-wizard__panels) {
  min-width: 0;
  max-width: 100%;
}

:deep(.mrt-step-panel) {
  width: min(100%, 46rem);
  margin-inline: auto;
  min-width: 0;
  padding: clamp(1.5rem, 4vw, 2.75rem);
  background: var(--mrt-wizard-green-dark);
  color: #ffffff;
  box-sizing: border-box;
}

:deep(.mrt-step-panel--search) {
  width: min(100%, 54rem);
  margin-top: 1.5rem;
  padding-block: clamp(1.75rem, 4vw, 3rem);
}

:deep(.mrt-step-panel--wide) {
  width: min(100%, 54rem);
}

:deep(.mrt-step-panel[data-wizard-step="date"]) {
  padding-bottom: clamp(1rem, 3vw, 1.5rem);
}

:deep(.mrt-step-panel--search .mrt-accent-btn--primary) {
  min-width: 12rem;
  padding: 0.85rem 2rem;
  font-size: 1.05rem;
  letter-spacing: 0.04em;
}

:deep(.mrt-heading--surface-title:focus) {
  outline: none;
}

:deep(.mrt-heading--surface-title:focus-visible) {
  outline: 3px solid var(--mrt-wizard-focus);
  outline-offset: 4px;
}

:deep(.mrt-combobox__input:focus-visible),
:deep(.mrt-segmented__option:focus-visible),
:deep(button:focus-visible),
:deep(a:focus-visible),
:deep(select:focus-visible) {
  outline: 3px solid var(--mrt-wizard-focus);
  outline-offset: 3px;
}

:deep(.mrt-step-panel > .mrt-text-secondary) {
  color: var(--mrt-color-on-dark-muted);
}

:deep(.mrt-ui-alert),
:deep(.mrt-surface--box) {
  border-radius: 0;
}

.mrt-wizard-shell-surfaces--embedded :deep(.mrt-step-panel--search) {
  margin-top: clamp(1rem, 2.5vw, 1.75rem);
  padding-block: clamp(2rem, 4vw, 3.25rem);
  padding-inline: clamp(1.75rem, 4vw, 3rem);
}

@media (min-width: 48.0625rem) {
  .mrt-wizard-shell-surfaces:not(.mrt-wizard-shell-surfaces--embedded) :deep(.mrt-wizard-main-card__errors) {
    max-width: none;
  }

  .mrt-wizard-shell-surfaces:not(.mrt-wizard-shell-surfaces--embedded) :deep(.mrt-step-panel),
  .mrt-wizard-shell-surfaces:not(.mrt-wizard-shell-surfaces--embedded) :deep(.mrt-step-panel--search),
  .mrt-wizard-shell-surfaces:not(.mrt-wizard-shell-surfaces--embedded) :deep(.mrt-step-panel--wide) {
    width: 100%;
    max-width: none;
    background: transparent;
  }
}

@media (max-width: 48rem) {
  .mrt-wizard-shell-surfaces :deep(.mrt-step-panel),
  .mrt-wizard-shell-surfaces :deep(.mrt-step-panel--search),
  .mrt-wizard-shell-surfaces :deep(.mrt-step-panel--wide) {
    width: 100%;
    min-width: 0;
    padding: 0.75rem 0.85rem 1.1rem;
    box-sizing: border-box;
  }
}
</style>
