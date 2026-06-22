<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    /** When true, optional backgroundImage spans the viewport (content stays capped). */
    bleedBackground?: boolean;
    backgroundImage?: string;
    /** When false, content slot is not capped (rare; default matches all public apps). */
    constrainContent?: boolean;
    /** When false, skip viewport gutter (e.g. embedded overview inside a padded panel). */
    contentPadding?: boolean;
  }>(),
  {
    bleedBackground: false,
    backgroundImage: '',
    constrainContent: true,
    contentPadding: true,
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
      :class="{
        'mrt-app-shell__content--fluid': !constrainContent,
        'mrt-app-shell__content--flush': !contentPadding,
      }"
    >
      <slot />
    </div>
  </div>
</template>

<style>
:root {
  /* Shell-specific; max-width scale in assets/mrt-layout-tokens.css */
  --mrt-app-bleed-outset: min(50px, 5vw);
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

.mrt-app-shell__content--flush {
  padding-inline: 0;
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
