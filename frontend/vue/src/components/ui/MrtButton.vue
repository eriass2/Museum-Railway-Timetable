<script setup lang="ts">
import { computed, useAttrs } from 'vue';
import type { MrtAdminButtonVariant, MrtPublicButtonVariant, MrtUiContext } from './types';
import { isAdminContext, mrtAdminButtonClass, mrtPublicButtonClass } from './uiContext';

defineOptions({ inheritAttrs: false });

const props = withDefaults(
  defineProps<{
    context?: MrtUiContext;
    variant?: MrtPublicButtonVariant | MrtAdminButtonVariant;
    href?: string;
    type?: 'button' | 'submit';
    disabled?: boolean;
    wide?: boolean;
  }>(),
  {
    context: 'public',
    type: 'button',
    disabled: false,
    variant: 'primary',
    wide: false,
  },
);

defineEmits<{ click: [] }>();

const attrs = useAttrs();

const buttonClass = computed(() => {
  const base = isAdminContext(props.context)
    ? mrtAdminButtonClass(props.variant as MrtAdminButtonVariant, props.wide)
    : mrtPublicButtonClass(props.variant as MrtPublicButtonVariant);
  const extra = attrs.class;
  if (!extra) {
    return base;
  }
  return [base, extra];
});
</script>

<template>
  <a v-if="href" :href="href" :class="buttonClass" v-bind="attrs">
    <slot />
  </a>
  <button
    v-else
    :type="type"
    :class="buttonClass"
    :disabled="disabled"
    v-bind="attrs"
    @click="$emit('click')"
  >
    <slot />
  </button>
</template>

<style scoped>
.mrt-accent-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 2.45rem;
  padding: 0.35rem 0.9rem;
  border: 0;
  border-radius: 0;
  font-size: 1.08rem;
  font-weight: 900;
  line-height: 1;
  text-decoration: none;
  cursor: pointer;
  box-sizing: border-box;
}

.mrt-accent-btn--primary {
  background: var(--mrt-wizard-yellow, var(--mrt-color-accent-600));
  color: var(--mrt-color-on-accent);
  text-transform: uppercase;
}

.mrt-accent-btn--select {
  min-width: 6.5rem;
  background: var(--mrt-wizard-yellow, var(--mrt-color-accent-600));
  color: var(--mrt-color-on-accent);
  text-transform: none;
}

.mrt-accent-btn--secondary {
  background: var(--mrt-bg-lighter, #f5f5f5);
  color: var(--mrt-text-secondary, #333);
  border: 1px solid var(--mrt-border-default, #ccc);
  font-weight: 600;
  text-transform: none;
}

.mrt-accent-btn--primary:hover,
.mrt-accent-btn--select:hover {
  background: var(--mrt-color-accent-700, #c9a01a);
  color: var(--mrt-color-on-accent);
}

.mrt-accent-btn--secondary:hover {
  background: var(--mrt-bg-light, #eee);
  color: var(--mrt-text-primary, #111);
}

.mrt-accent-btn:active {
  transform: translateY(1px);
}

.mrt-accent-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}
</style>
