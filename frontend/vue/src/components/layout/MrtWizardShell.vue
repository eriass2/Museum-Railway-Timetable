<script setup lang="ts">
import { computed, type CSSProperties } from 'vue';
import MrtPublicAppShell from './MrtPublicAppShell.vue';
import MrtWizardHero from './MrtWizardHero.vue';
import MrtWizardShellSurfaces from './MrtWizardShellSurfaces.vue';

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
    <MrtPublicAppShell
      :bleed-background="bleedBackground"
      :background-image="heroBackgroundUrl"
      :constrain-content="false"
      :content-padding="false"
    >
      <MrtWizardHero
        :step="step"
        :hero-class="heroClass"
        :hero-section-style="heroSectionStyle"
      >
        <MrtWizardShellSurfaces>
          <slot />
        </MrtWizardShellSurfaces>
      </MrtWizardHero>
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

.mrt-journey-wizard--embedded {
  margin-top: clamp(2rem, 4vw, 3rem);
  margin-bottom: clamp(2.5rem, 5vw, 4rem);
}

.mrt-journey-wizard :deep(.mrt-app-shell__content) {
  padding-inline: 0;
  margin-inline: auto;
}

.mrt-journey-wizard:not(.mrt-journey-wizard--embedded) :deep(.mrt-app-shell__content) {
  max-width: var(--mrt-wizard-content-max);
}

.mrt-journey-wizard :deep(.mrt-journey-wizard__hero--has-bg) {
  background: transparent;
}

@media (max-width: 48rem) {
  .mrt-journey-wizard {
    box-sizing: border-box;
    width: 100svw;
    max-width: 100svw;
    margin-left: calc(50% - 50svw);
    margin-right: calc(50% - 50svw);
    --mrt-app-content-max: 100%;
  }

  .mrt-journey-wizard :deep(.mrt-app-shell),
  .mrt-journey-wizard :deep(.mrt-app-shell__content) {
    width: 100%;
    max-width: none;
  }

  .mrt-journey-wizard:not(.mrt-journey-wizard--embedded) :deep(.mrt-app-shell__content) {
    max-width: none;
  }
}
</style>
