<script setup lang="ts">
import { computed } from 'vue';

type StackSpace = 'none' | 'xs' | 'sm' | 'md' | 'lg' | 'xl';

const props = withDefaults(
  defineProps<{
    as?: string;
    gap?: StackSpace;
    marginTop?: StackSpace;
    marginBottom?: StackSpace;
  }>(),
  {
    as: 'div',
    gap: 'none',
    marginTop: 'none',
    marginBottom: 'none',
  },
);

const spaceVar: Record<Exclude<StackSpace, 'none'>, string> = {
  xs: 'var(--mrt-spacing-xs)',
  sm: 'var(--mrt-spacing-sm)',
  md: 'var(--mrt-spacing-md)',
  lg: 'var(--mrt-spacing-lg)',
  xl: 'var(--mrt-spacing-xl)',
};

const stackClass = computed(() => ({
  'mrt-stack--gap-xs': props.gap === 'xs',
  'mrt-stack--gap-sm': props.gap === 'sm',
  'mrt-stack--gap-md': props.gap === 'md',
  'mrt-stack--gap-lg': props.gap === 'lg',
  'mrt-stack--gap-xl': props.gap === 'xl',
  'mrt-stack--mt-xs': props.marginTop === 'xs',
  'mrt-stack--mt-sm': props.marginTop === 'sm',
  'mrt-stack--mt-md': props.marginTop === 'md',
  'mrt-stack--mt-lg': props.marginTop === 'lg',
  'mrt-stack--mt-xl': props.marginTop === 'xl',
  'mrt-stack--mb-xs': props.marginBottom === 'xs',
  'mrt-stack--mb-sm': props.marginBottom === 'sm',
  'mrt-stack--mb-md': props.marginBottom === 'md',
  'mrt-stack--mb-lg': props.marginBottom === 'lg',
  'mrt-stack--mb-xl': props.marginBottom === 'xl',
}));
</script>

<template>
  <component :is="as" class="mrt-stack" :class="stackClass">
    <slot />
  </component>
</template>

<style scoped>
.mrt-stack {
  display: flex;
  flex-direction: column;
}

.mrt-stack--gap-xs {
  gap: var(--mrt-spacing-xs);
}

.mrt-stack--gap-sm {
  gap: var(--mrt-spacing-sm);
}

.mrt-stack--gap-md {
  gap: var(--mrt-spacing-md);
}

.mrt-stack--gap-lg {
  gap: var(--mrt-spacing-lg);
}

.mrt-stack--gap-xl {
  gap: var(--mrt-spacing-xl);
}

.mrt-stack--mt-xs {
  margin-top: var(--mrt-spacing-xs);
}

.mrt-stack--mt-sm {
  margin-top: var(--mrt-spacing-sm);
}

.mrt-stack--mt-md {
  margin-top: var(--mrt-spacing-md);
}

.mrt-stack--mt-lg {
  margin-top: var(--mrt-spacing-lg);
}

.mrt-stack--mt-xl {
  margin-top: var(--mrt-spacing-xl);
}

.mrt-stack--mb-xs {
  margin-bottom: var(--mrt-spacing-xs);
}

.mrt-stack--mb-sm {
  margin-bottom: var(--mrt-spacing-sm);
}

.mrt-stack--mb-md {
  margin-bottom: var(--mrt-spacing-md);
}

.mrt-stack--mb-lg {
  margin-bottom: var(--mrt-spacing-lg);
}

.mrt-stack--mb-xl {
  margin-bottom: var(--mrt-spacing-xl);
}
</style>
