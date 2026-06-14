<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    /** When true, optional backgroundImage spans the viewport (content stays capped). */
    bleedBackground?: boolean;
    backgroundImage?: string;
    /** When false, content slot is not capped (rare; default matches all public apps). */
    constrainContent?: boolean;
  }>(),
  {
    bleedBackground: false,
    backgroundImage: '',
    constrainContent: true,
  },
);

const shellClasses = computed(() => ({
  'mrt-app-shell--bleed-bg': props.bleedBackground,
}));

const backdropStyle = computed(() => {
  const url = String(props.backgroundImage || '').trim();
  if (!url) {
    return undefined;
  }
  return { '--mrt-app-shell-bg-image': `url(${JSON.stringify(url)})` };
});
</script>

<template>
  <div class="mrt-app-shell" :class="shellClasses">
    <div
      v-if="bleedBackground"
      class="mrt-app-shell__backdrop"
      aria-hidden="true"
      :style="backdropStyle"
    />
    <div
      class="mrt-app-shell__content"
      :class="{ 'mrt-app-shell__content--fluid': !constrainContent }"
    >
      <slot />
    </div>
  </div>
</template>

<style>
:root {
  --mrt-app-content-max: min(96vw, 80rem);
  --mrt-wizard-content-max: min(76.8vw, 64rem);
  --mrt-app-bleed-outset: min(50px, 5vw);
  --mrt-app-shell-padding-inline: clamp(1rem, 3vw, 1.25rem);
}
</style>

<style scoped>
.mrt-app-shell {
  position: relative;
  box-sizing: border-box;
  width: 100%;
  max-width: 100%;
}

.mrt-app-shell__content {
  position: relative;
  z-index: 1;
  box-sizing: border-box;
  width: 100%;
  max-width: var(--mrt-app-content-max);
  margin-inline: auto;
  padding-inline: var(--mrt-app-shell-padding-inline);
}

.mrt-app-shell__content--fluid {
  max-width: none;
}

.mrt-app-shell__backdrop {
  display: none;
}

@media (min-width: 48.0625rem) {
  .mrt-app-shell--bleed-bg {
    width: 100vw;
    width: 100svw;
    max-width: none;
    margin-left: calc(-1 * var(--mrt-app-bleed-outset));
    margin-right: calc(100% - 100svw + var(--mrt-app-bleed-outset));
  }

  .mrt-app-shell--bleed-bg .mrt-app-shell__backdrop {
    display: block;
    position: absolute;
    z-index: 0;
    inset: 0;
    background-color: var(--mrt-wizard-green-dark, #1e4d6b);
    background-image: var(--mrt-app-shell-bg-image, none);
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    pointer-events: none;
  }
}
</style>
