<script setup lang="ts">
defineOptions({ inheritAttrs: false });

withDefaults(
  defineProps<{
    level?: 'h2' | 'h3' | 'h4' | 'h5';
    size?: 'lg' | 'md' | 'xl' | 'sm';
    /** Centered hero title on wizard route surface. */
    variant?: 'default' | 'surface-title';
  }>(),
  {
    level: 'h2',
    size: 'lg',
    variant: 'default',
  },
);
</script>

<template>
  <component
    :is="level"
    class="mrt-heading"
    :class="[
      size !== 'lg' ? `mrt-heading--${size}` : undefined,
      variant === 'surface-title' ? 'mrt-heading--surface-title' : undefined,
    ]"
    v-bind="$attrs"
  >
    <slot />
  </component>
</template>

<style scoped>
@import './mrtFocusRing.css';

.mrt-heading {
  margin: 0;
  font-family: var(--mrt-font-heading);
  font-weight: var(--mrt-font-weight-heading);
}

.mrt-heading--lg {
  font-size: var(--mrt-font-lg, 1.25rem);
}

.mrt-heading--md {
  font-size: 1.35rem;
  font-weight: 900;
}

.mrt-heading--sm {
  font-size: var(--mrt-font-sm, 0.875rem);
}

.mrt-heading--xl {
  font-size: clamp(2rem, 4vw, 2.6rem);
  line-height: 1.08;
  font-weight: 700;
}

.mrt-heading--surface-title {
  margin: 0 0 1.25rem;
  color: var(--mrt-color-green-700, #1f4d2e);
  text-align: center;
  font-size: clamp(2rem, 4vw, 2.6rem);
  line-height: 1.08;
  font-weight: 700;
}

@media (max-width: 48rem) {
  .mrt-heading--surface-title {
    font-size: 1.8rem;
  }
}
</style>
