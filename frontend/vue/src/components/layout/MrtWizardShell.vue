<script setup lang="ts">
import { computed, type CSSProperties } from 'vue';
import MrtPublicAppShell from './MrtPublicAppShell.vue';

const props = withDefaults(
  defineProps<{
    embedded?: boolean;
    step?: string;
    debug?: boolean;
    heroBackgroundUrl?: string;
    bleedBackground?: boolean;
  }>(),
  {
    embedded: false,
    step: 'route',
    debug: false,
    heroBackgroundUrl: '',
    bleedBackground: false,
  },
);

const rootClass = computed(() => ({
  'mrt-journey-wizard--embedded': props.embedded,
  'mrt-journey-wizard--debug': props.debug,
}));

const heroClass = computed(() => ({
  'mrt-journey-wizard__hero--has-bg': props.heroBackgroundUrl !== '',
}));

const heroSectionStyle = computed((): CSSProperties | undefined => {
  if (props.bleedBackground || !props.heroBackgroundUrl) {
    return undefined;
  }
  return {
    '--mrt-wizard-hero-bg-image': `url(${JSON.stringify(props.heroBackgroundUrl)})`,
  };
});
</script>

<template>
  <div class="mrt-journey-wizard" :class="rootClass" :data-step="step">
    <MrtPublicAppShell :bleed-background="bleedBackground" :background-image="heroBackgroundUrl">
      <section
        class="mrt-journey-wizard__hero"
        :class="heroClass"
        :style="heroSectionStyle"
      >
        <div class="mrt-journey-wizard__hero-inner">
          <slot />
        </div>
      </section>
    </MrtPublicAppShell>
  </div>
</template>

<style scoped>
.mrt-journey-wizard {
  color: var(--mrt-wizard-text);
  max-width: 100%;
  min-width: 0;
}

@media (prefers-reduced-motion: reduce) {
  .mrt-journey-wizard :deep(*),
  .mrt-journey-wizard :deep(*::before),
  .mrt-journey-wizard :deep(*::after) {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

.mrt-journey-wizard__hero {
  box-sizing: border-box;
  position: relative;
  width: 100%;
  min-height: 0;
  padding: clamp(3rem, 8vw, 7rem) 1rem clamp(2rem, 5vw, 3rem);
  background: var(--mrt-wizard-green-dark);
}

.mrt-journey-wizard:not(.mrt-journey-wizard--embedded) .mrt-journey-wizard__hero {
  padding: clamp(2rem, 5vw, 4rem) 1rem clamp(1.5rem, 4vw, 2.5rem);
}

.mrt-journey-wizard[data-step="route"] .mrt-journey-wizard__hero {
  min-height: 0;
}

.mrt-journey-wizard__hero-inner {
  width: min(100%, 58rem);
  margin-inline: auto;
  min-width: 0;
  max-width: 100%;
}

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

.mrt-journey-wizard--embedded {
  margin-top: clamp(2rem, 4vw, 3rem);
  margin-bottom: clamp(2.5rem, 5vw, 4rem);
}

.mrt-journey-wizard--embedded .mrt-journey-wizard__hero:not(.mrt-journey-wizard__hero--has-bg) {
  margin-left: 0;
  margin-right: 0;
  width: 100%;
  max-width: 100%;
  padding: clamp(1.5rem, 4vw, 2.5rem) clamp(1rem, 3vw, 1.75rem) clamp(2rem, 5vw, 3rem);
  background: var(--mrt-wizard-surface);
  color: var(--mrt-wizard-text);
}

.mrt-journey-wizard--embedded .mrt-journey-wizard__hero--has-bg {
  margin-left: 0;
  margin-right: 0;
  width: 100%;
  max-width: 100%;
  padding: clamp(1.5rem, 4vw, 2.5rem) clamp(1rem, 3vw, 1.75rem) clamp(2rem, 5vw, 3rem);
  color: #ffffff;
  position: relative;
  background-color: var(--mrt-wizard-green-dark);
  background-image: var(--mrt-wizard-hero-bg-image);
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}

.mrt-journey-wizard--embedded .mrt-journey-wizard__hero--has-bg::before {
  content: '';
  position: absolute;
  inset: 0;
  background: color-mix(in srgb, var(--mrt-wizard-green-dark) 30%, transparent);
  pointer-events: none;
}

.mrt-journey-wizard:not(.mrt-journey-wizard--embedded) .mrt-journey-wizard__hero--has-bg {
  background: transparent;
}

.mrt-journey-wizard__hero--has-bg > .mrt-journey-wizard__hero-inner {
  position: relative;
  z-index: 1;
}

.mrt-journey-wizard--embedded[data-step="route"] .mrt-journey-wizard__hero {
  min-height: auto;
}

.mrt-journey-wizard--embedded :deep(.mrt-step-panel--search) {
  margin-top: clamp(1rem, 2.5vw, 1.75rem);
  padding-block: clamp(2rem, 4vw, 3.25rem);
  padding-inline: clamp(1.75rem, 4vw, 3rem);
}

.mrt-journey-wizard:not(.mrt-journey-wizard--embedded) :deep(.mrt-app-shell__content) {
  max-width: var(--mrt-wizard-content-max);
}

.mrt-journey-wizard--embedded .mrt-journey-wizard__hero--has-bg :deep(.mrt-step-panel--search) {
  background: var(--mrt-wizard-surface);
  color: var(--mrt-wizard-text);
  padding-inline: clamp(1.75rem, 4vw, 3rem);
}

.mrt-journey-wizard--embedded .mrt-journey-wizard__hero:not(.mrt-journey-wizard__hero--has-bg) :deep(.mrt-step-panel--search) {
  background: transparent;
  color: var(--mrt-wizard-text);
  padding-inline: 0;
}

@media (min-width: 48.0625rem) {
  .mrt-journey-wizard:not(.mrt-journey-wizard--embedded) .mrt-journey-wizard__hero-inner {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: none;
  }

  .mrt-journey-wizard:not(.mrt-journey-wizard--embedded) :deep(.mrt-wizard-main-card__errors) {
    max-width: none;
  }

  .mrt-journey-wizard:not(.mrt-journey-wizard--embedded) :deep(.mrt-step-panel),
  .mrt-journey-wizard:not(.mrt-journey-wizard--embedded) :deep(.mrt-step-panel--search),
  .mrt-journey-wizard:not(.mrt-journey-wizard--embedded) :deep(.mrt-step-panel--wide) {
    width: 100%;
    max-width: none;
    background: transparent;
  }
}

@media (max-width: 48rem) {
  .mrt-journey-wizard__hero {
    padding: 1rem;
  }

  .mrt-journey-wizard :deep(.mrt-step-panel),
  .mrt-journey-wizard :deep(.mrt-step-panel--search),
  .mrt-journey-wizard :deep(.mrt-step-panel--wide) {
    width: 100%;
    min-width: 0;
    padding: 0.75rem 0.85rem 1.1rem;
    box-sizing: border-box;
  }
}
</style>
