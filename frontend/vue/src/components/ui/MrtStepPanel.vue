<script setup lang="ts">
withDefaults(
  defineProps<{
    step: string;
    ariaLabel: string;
    variant?: 'default' | 'search' | 'wide';
  }>(),
  { variant: 'default' },
);
</script>

<template>
  <div
    :data-wizard-step="step"
    class="mrt-step-panel mrt-step-panel--active"
    :class="{
      'mrt-step-panel--search': variant === 'search',
      'mrt-step-panel--wide': variant === 'wide',
    }"
    role="region"
    :aria-label="ariaLabel"
  >
    <slot />
  </div>
</template>

<style scoped>
.mrt-step-panel {
  width: var(--mrt-step-panel-width, min(100%, var(--mrt-max-step)));
  max-width: var(--mrt-step-panel-max-width, none);
  margin-inline: var(--mrt-step-panel-margin-inline, auto);
  margin-top: var(--mrt-step-panel-margin-top, 0);
  min-width: 0;
  padding: var(--mrt-step-panel-padding, clamp(1.5rem, 4vw, 2.75rem));
  background: var(--mrt-step-panel-bg, var(--mrt-wizard-green-dark));
  color: var(--mrt-step-panel-color, #ffffff);
  box-sizing: border-box;
  --mrt-surface-radius: var(--mrt-radius-sm, 0);
  --mrt-text-secondary-color: var(--mrt-color-on-dark-muted);
}

.mrt-step-panel--search {
  width: var(--mrt-step-panel-search-width, min(100%, var(--mrt-max-step-wide)));
  margin-top: var(--mrt-step-panel-search-margin-top, 1.5rem);
  padding-block: var(--mrt-step-panel-search-padding-block, clamp(1.75rem, 4vw, 3rem));
  padding-inline: var(
    --mrt-step-panel-search-padding-inline,
    var(--mrt-step-panel-padding-inline, unset)
  );
  background: var(--mrt-step-panel-search-bg, var(--mrt-step-panel-bg, var(--mrt-wizard-green-dark)));
  color: var(--mrt-step-panel-search-color, var(--mrt-step-panel-color, #ffffff));
  --mrt-heading-surface-color: var(
    --mrt-step-panel-search-color,
    var(--mrt-step-panel-color, #ffffff)
  );
}

.mrt-step-panel--wide {
  width: var(--mrt-step-panel-wide-width, min(100%, var(--mrt-max-step-wide)));
}

.mrt-step-panel[data-wizard-step='date'] {
  padding-bottom: var(--mrt-step-panel-date-padding-bottom, clamp(1rem, 3vw, 1.5rem));
}
</style>

<style>
.mrt-step-panel .mrt-text-secondary {
  color: var(--mrt-text-secondary-color, var(--mrt-color-on-dark-muted));
}

.mrt-step-panel .mrt-ui-alert,
.mrt-step-panel .mrt-surface--box {
  border-radius: var(--mrt-surface-radius, 0);
}
</style>
