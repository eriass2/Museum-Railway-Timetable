<script setup lang="ts">
defineOptions({ inheritAttrs: false });

withDefaults(
  defineProps<{
    level?: 'h2' | 'h3' | 'h4';
    size?: 'lg' | 'md' | 'xl';
  }>(),
  {
    level: 'h2',
    size: 'lg',
  },
);
</script>

<template>
  <component
    :is="level"
    class="mrt-heading"
    :class="size !== 'lg' ? `mrt-heading--${size}` : undefined"
    v-bind="$attrs"
  >
    <slot />
  </component>
</template>
