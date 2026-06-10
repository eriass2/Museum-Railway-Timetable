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
