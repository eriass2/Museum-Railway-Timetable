<script setup lang="ts">
import type { CSSProperties } from 'vue';

defineProps<{
  embedded?: boolean;
  step?: string;
  heroClass?: Record<string, boolean>;
  heroSectionStyle?: CSSProperties;
}>();
</script>

<template>
  <section
    class="mrt-journey-wizard__hero"
    :class="[
      heroClass,
      { 'mrt-journey-wizard__hero--embedded': embedded },
    ]"
    :style="heroSectionStyle"
    :data-step="step"
  >
    <div class="mrt-journey-wizard__hero-inner">
      <slot />
    </div>
  </section>
</template>

<style scoped>
.mrt-journey-wizard__hero {
  box-sizing: border-box;
  position: relative;
  width: 100%;
  min-height: 0;
  padding: clamp(3rem, 8vw, 7rem) 1rem clamp(2rem, 5vw, 3rem);
  background: var(--mrt-wizard-green-dark);
}

.mrt-journey-wizard__hero:not(.mrt-journey-wizard__hero--embedded) {
  padding: clamp(2rem, 5vw, 4rem) 1rem clamp(1.5rem, 4vw, 2.5rem);
}

.mrt-journey-wizard__hero[data-step="route"] {
  min-height: 0;
}

.mrt-journey-wizard__hero-inner {
  width: min(100%, 58rem);
  margin-inline: auto;
  min-width: 0;
  max-width: 100%;
}

.mrt-journey-wizard__hero--embedded {
  margin-top: 0;
}

.mrt-journey-wizard__hero--embedded:not(.mrt-journey-wizard__hero--has-bg) {
  margin-left: 0;
  margin-right: 0;
  width: 100%;
  max-width: 100%;
  padding: clamp(1.5rem, 4vw, 2.5rem) clamp(1rem, 3vw, 1.75rem) clamp(2rem, 5vw, 3rem);
  background: var(--mrt-wizard-surface);
  color: var(--mrt-wizard-text);
}

.mrt-journey-wizard__hero--embedded.mrt-journey-wizard__hero--has-bg {
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

.mrt-journey-wizard__hero--embedded.mrt-journey-wizard__hero--has-bg::before {
  content: '';
  position: absolute;
  inset: 0;
  background: color-mix(in srgb, var(--mrt-wizard-green-dark) 30%, transparent);
  pointer-events: none;
}

.mrt-journey-wizard__hero--embedded:not(.mrt-journey-wizard__hero--has-bg) {
  background: var(--mrt-wizard-surface);
}

.mrt-journey-wizard__hero--has-bg > .mrt-journey-wizard__hero-inner {
  position: relative;
  z-index: 1;
}

.mrt-journey-wizard__hero--embedded[data-step="route"] {
  min-height: auto;
}

.mrt-journey-wizard__hero--embedded.mrt-journey-wizard__hero--has-bg {
  --mrt-step-panel-search-bg: var(--mrt-wizard-surface);
  --mrt-step-panel-search-color: var(--mrt-wizard-text);
  --mrt-step-panel-search-padding-inline: clamp(1.75rem, 4vw, 3rem);
}

.mrt-journey-wizard__hero--embedded:not(.mrt-journey-wizard__hero--has-bg) {
  --mrt-step-panel-search-bg: transparent;
  --mrt-step-panel-search-color: var(--mrt-wizard-text);
  --mrt-step-panel-search-padding-inline: 0;
}

@media (min-width: 48.0625rem) {
  .mrt-journey-wizard__hero:not(.mrt-journey-wizard__hero--embedded) .mrt-journey-wizard__hero-inner {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: none;
  }
}

@media (max-width: 48rem) {
  .mrt-journey-wizard__hero {
    padding: 1rem;
  }
}
</style>
