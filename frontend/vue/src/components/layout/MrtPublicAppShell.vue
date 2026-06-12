<script setup lang="ts">
import { computed } from 'vue';
import '../../styles/app-shell.css';

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
