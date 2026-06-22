<script setup lang="ts">
import type { CSSProperties } from 'vue';

defineProps<{
  step?: string;
  heroClass?: Record<string, boolean>;
  heroSectionStyle?: CSSProperties;
}>();
</script>

<template>
  <section
    class="mrt-journey-wizard__hero"
    :class="heroClass"
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
  padding: clamp(2rem, 5vw, 4rem) 1rem clamp(1.5rem, 4vw, 2.5rem);
  background: var(--mrt-wizard-green-dark);
}

.mrt-journey-wizard__hero[data-step="route"] {
  min-height: 0;
}

.mrt-journey-wizard__hero-inner {
  width: min(100%, var(--mrt-wizard-hero-inner-max, 58rem));
  margin-inline: auto;
  min-width: 0;
  max-width: 100%;
}

.mrt-journey-wizard__hero--has-bg > .mrt-journey-wizard__hero-inner {
  position: relative;
  z-index: 1;
}

@media (min-width: 48.0625rem) {
  .mrt-journey-wizard__hero .mrt-journey-wizard__hero-inner {
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
